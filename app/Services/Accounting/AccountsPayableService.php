<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\PurchaseBillCreateData;
use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\DTOs\PurchaseBillItemData;
use App\DTOs\VendorBillIngestionData;
use App\Models\Account;
use App\Models\BillItem;
use App\Models\Bir2307Certificate;
use App\Models\DoctorProfile;
use App\Models\JournalEntry;
use App\Models\PurchaseBill;
use App\Models\ThreeWayMatch;
use App\Models\Vendor;
use DomainException;
use Illuminate\Support\Facades\DB;

final class AccountsPayableService
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService,
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    /**
     * Ingest vendor bill from API/DTO, execute 3-Way Match validation, calculate BIR Form 2307 EWT,
     * and automatically post balanced Double-Entry AP journal entry.
     */
    public function ingestVendorBillAndPostAP(VendorBillIngestionData|PurchaseBillCreateData $data): PurchaseBill
    {
        return DB::transaction(function () use ($data): PurchaseBill {
            $vendor = Vendor::findOrFail($data->vendorId);
            $doctor = property_exists($data, 'doctorId') && $data->doctorId ? DoctorProfile::find($data->doctorId) : null;

            // 1. Calculate Gross, BIR EWT Withholding, and Net Payable across line items
            $totalGross = '0.0000';
            $totalEwt = '0.0000';
            $totalNetPayable = '0.0000';
            $calculatedItems = [];

            foreach ($data->items as $item) {
                $qty = is_array($item) ? (string) ($item['quantity'] ?? '1') : (string) $item->quantity;
                $unitPrice = is_array($item) ? (string) ($item['unit_price'] ?? '0') : (string) $item->unitPrice;
                $gross = bcmul($qty, $unitPrice, 4);
                $totalGross = bcadd($totalGross, $gross, 4);

                $atc = is_array($item) ? ($item['atc_code'] ?? 'WI158') : ($item->atcCode ?? 'WI158');

                // Determine ATC Tax Rate: 1% goods, 2% services, 10% medical PF
                $ewtRate = match (strtoupper($atc)) {
                    'WI158', 'WC158' => '0.0100', // 1% Goods
                    'WI160', 'WC160' => '0.0200', // 2% Services
                    'WI010'          => '0.1000', // 10% Medical PF (Individual)
                    'WI020'          => '0.1500', // 15% Medical PF (Individual >3M)
                    default          => '0.0100',
                };

                // Apply Doctor override if sworn declaration submitted (5% vs 10%)
                if ($doctor && $doctor->has_sworn_declaration) {
                    $ewtRate = '0.0500';
                }

                $ewtAmount = bcmul($gross, $ewtRate, 4);
                $netPayable = bcsub($gross, $ewtAmount, 4);

                $totalEwt = bcadd($totalEwt, $ewtAmount, 4);
                $totalNetPayable = bcadd($totalNetPayable, $netPayable, 4);

                $calculatedItems[] = [
                    'itemCode'    => is_array($item) ? ($item['item_code'] ?? 'ITEM') : $item->itemCode,
                    'description' => is_array($item) ? ($item['description'] ?? 'Item Description') : $item->description,
                    'expenseType' => is_array($item) ? ($item['expense_type'] ?? 'GOODS_INVENTORY') : $item->expenseType,
                    'quantity'    => $qty,
                    'unitPrice'   => $unitPrice,
                    'gross'       => $gross,
                    'atcCode'     => $atc,
                    'ewtRate'     => $ewtRate,
                    'ewtAmount'   => $ewtAmount,
                    'netPayable'  => $netPayable,
                ];
            }

            // 2. Perform 3-Way Match Verification (PO vs GRN vs Vendor Invoice)
            $poNumber = property_exists($data, 'poNumber') ? $data->poNumber : null;
            $grnNumber = property_exists($data, 'grnNumber') ? $data->grnNumber : null;

            $hasPo = !empty($poNumber) && property_exists($data, 'poAmount') && $data->poAmount !== null;
            $hasGrn = !empty($grnNumber) && property_exists($data, 'grnAmount') && $data->grnAmount !== null;

            $poAmount = $hasPo ? (string) $data->poAmount : '0.0000';
            $grnAmount = $hasGrn ? (string) $data->grnAmount : '0.0000';

            $priceVariance = bcsub($totalGross, $poAmount, 4);
            $receiptVariance = bcsub($totalGross, $grnAmount, 4);

            if (!$hasPo || !$hasGrn) {
                $matchStatus = 'PENDING_GRN';
            } elseif (bccomp($priceVariance, '0.0000', 4) !== 0) {
                $matchStatus = bccomp($priceVariance, '0.0000', 4) > 0 ? 'OVER_BILLED' : 'PRICE_MISMATCH';
            } elseif (bccomp($receiptVariance, '0.0000', 4) !== 0) {
                $matchStatus = 'QTY_MISMATCH';
            } else {
                $matchStatus = 'MATCHED';
            }

            // 3. Create Master Purchase Bill
            $billNumber = property_exists($data, 'billNumber') && $data->billNumber
                ? $data->billNumber
                : ('BILL-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))));

            $bill = PurchaseBill::create([
                'bill_number'  => $billNumber,
                'vendor_id'    => $vendor->id,
                'bill_date'    => $data->billDate,
                'due_date'     => $data->dueDate,
                'total_amount' => $totalGross,
                'paid_amount'  => '0.0000',
                'status'       => ($matchStatus === 'MATCHED') ? 'APPROVED' : 'UNPAID',
            ]);

            // 4. Record 3-Way Match Record
            ThreeWayMatch::create([
                'purchase_bill_id'      => $bill->id,
                'po_number'             => $data->poNumber,
                'grn_number'            => $data->grnNumber,
                'vendor_invoice_number' => $data->vendorInvoiceNumber,
                'po_amount'             => $poAmount,
                'grn_amount'            => $grnAmount,
                'invoice_amount'        => $totalGross,
                'price_variance'        => $priceVariance,
                'quantity_variance'     => '0.00',
                'match_status'          => $matchStatus,
                'approved_by'           => ($matchStatus === 'MATCHED' && auth()->check()) ? auth()->id() : null,
                'approved_at'           => ($matchStatus === 'MATCHED') ? now() : null,
            ]);

            // 5. Persist Bill Items
            foreach ($calculatedItems as $c) {
                BillItem::create([
                    'purchase_bill_id' => $bill->id,
                    'item_code'        => $c['itemCode'],
                    'description'      => $c['description'],
                    'expense_type'     => $c['expenseType'],
                    'quantity'         => $c['quantity'],
                    'unit_price'       => $c['unitPrice'],
                    'gross_amount'     => $c['gross'],
                    'atc_code'         => $c['atcCode'],
                    'ewt_rate'         => $c['ewtRate'],
                    'ewt_amount'       => $c['ewtAmount'],
                    'net_payable'      => $c['netPayable'],
                ]);
            }

            // 6. Generate BIR Form 2307 Certificate
            if (bccomp($totalEwt, '0.0000', 4) > 0) {
                $certNum = '2307-' . date('Y') . '-' . str_pad((string) (Bir2307Certificate::count() + 1), 6, '0', STR_PAD_LEFT);
                Bir2307Certificate::create([
                    'certificate_number' => $certNum,
                    'purchase_bill_id'   => $bill->id,
                    'vendor_id'          => $vendor->id,
                    'doctor_id'          => $doctor?->id,
                    'period_from'        => $data->billDate,
                    'period_to'          => $data->dueDate,
                    'payee_name'         => $doctor ? $doctor->full_name : $vendor->name,
                    'payee_tin'          => $doctor ? $doctor->tin : ($vendor->tin ?? '000-000-000-000'),
                    'atc_code'           => $calculatedItems[0]['atcCode'] ?? 'WI158',
                    'tax_base_amount'    => $totalGross,
                    'tax_rate'           => $calculatedItems[0]['ewtRate'] ?? '0.0100',
                    'tax_withheld'       => $totalEwt,
                    'form_status'        => 'GENERATED',
                ]);
            }

            // 7. Post General Ledger AP Double-Entry Journal ONLY if 3-Way Match is Verified
            if ($matchStatus === 'MATCHED') {
                $this->postAPDoubleEntry($bill, $data->billDate, $vendor, $totalGross, $totalNetPayable, $totalEwt, $calculatedItems);
            }

            // Log event in BIR CAS audit trail
            $this->auditTrailService->logFinancialEvent(
                auditable: $bill,
                action: 'INSERT',
                oldValues: null,
                newValues: $bill->toArray(),
                userId: auth()->id(),
                userName: auth()->user()?->name ?? 'System Service',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $bill->loadMissing(['items', 'threeWayMatch', 'birCertificate', 'vendor']);
        });
    }

    /**
     * Post balanced Double-Entry AP journal entry for an approved/verified Purchase Bill.
     */
    public function postApprovedBillDoubleEntry(PurchaseBill $bill): void
    {
        if (JournalEntry::where('reference_number', 'JE-AP-' . $bill->bill_number)->exists()) {
            return;
        }

        $bill->loadMissing(['vendor', 'items']);
        $billDate = $bill->bill_date instanceof \DateTimeInterface
            ? $bill->bill_date->format('Y-m-d')
            : (string) $bill->bill_date;

        $totalGross = (string) $bill->total_amount;
        $totalEwt = '0.0000';
        foreach ($bill->items as $item) {
            $totalEwt = bcadd($totalEwt, (string) $item->ewt_amount, 4);
        }
        $totalNetPayable = bcsub($totalGross, $totalEwt, 4);

        $this->postAPDoubleEntry(
            bill: $bill,
            billDate: $billDate,
            vendor: $bill->vendor,
            totalGross: $totalGross,
            netPayable: $totalNetPayable,
            ewtAmount: $totalEwt,
            items: $bill->items
        );
    }

    private function postAPDoubleEntry(
        PurchaseBill $bill,
        string $billDate,
        Vendor $vendor,
        string $totalGross,
        string $netPayable,
        string $ewtAmount,
        iterable $items = []
    ): void {
        $journalLines = [];
        $allocatedGross = '0.0000';

        // Categorized GL line distribution based on line item expense classifications
        foreach ($items as $item) {
            $gross = is_array($item)
                ? (string) ($item['gross'] ?? $item['gross_amount'] ?? '0')
                : (string) ($item->gross_amount ?? $item->gross ?? '0');

            if (bccomp($gross, '0.0000', 4) <= 0) {
                continue;
            }

            $type = is_array($item)
                ? ($item['expense_type'] ?? $item['expenseType'] ?? 'GOODS_INVENTORY')
                : ($item->expense_type ?? $item->expenseType ?? 'GOODS_INVENTORY');

            $accConfig = match ($type) {
                'DOCTOR_PROFESSIONAL_FEE' => ['code' => '5030', 'name' => 'Physician & Specialist Professional Fees', 'category' => 'EXPENSE'],
                'CAPEX_EQUIPMENT'         => ['code' => '1500', 'name' => 'Hospital & Medical Equipment Assets', 'category' => 'ASSET'],
                'SERVICES_MAINTENANCE'    => ['code' => '5040', 'name' => 'Repairs & Hospital Facilities Maintenance', 'category' => 'EXPENSE'],
                'UTILITIES'               => ['code' => '5050', 'name' => 'Hospital Utilities Expense (Power & Water)', 'category' => 'EXPENSE'],
                default                   => ['code' => '5020', 'name' => 'Medical & Hospital Operating Supplies', 'category' => 'EXPENSE'],
            };

            $expenseAcc = Account::firstOrCreate(
                ['code' => $accConfig['code']],
                ['name' => $accConfig['name'], 'category' => $accConfig['category'], 'normal_balance' => 'DEBIT']
            );

            $journalLines[] = new JournalLineData(
                accountId: $expenseAcc->id,
                debit: $gross,
                credit: '0.0000',
                memo: "Procurement line item ({$type}) on {$bill->bill_number} from {$vendor->name}"
            );

            $allocatedGross = bcadd($allocatedGross, $gross, 4);
        }

        // Fallback for unitemized gross balance
        $unallocatedGross = bcsub($totalGross, $allocatedGross, 4);
        if (bccomp($unallocatedGross, '0.0000', 4) > 0) {
            $defaultExpenseAcc = Account::firstOrCreate(
                ['code' => '5020'],
                ['name' => 'Medical & Hospital Operating Supplies', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']
            );
            $journalLines[] = new JournalLineData(
                accountId: $defaultExpenseAcc->id,
                debit: $unallocatedGross,
                credit: '0.0000',
                memo: "Unallocated procurement gross expense on {$bill->bill_number} from {$vendor->name}"
            );
        }

        $apVendorAcc = Account::firstOrCreate(
            ['code' => '2010'],
            ['name' => 'Accounts Payable - Vendors & Suppliers', 'category' => 'LIABILITY', 'normal_balance' => 'CREDIT']
        );

        $ewtPayableAcc = Account::firstOrCreate(
            ['code' => '2030'],
            ['name' => 'Withholding Tax Payable - Expanded (BIR 2307)', 'category' => 'LIABILITY', 'normal_balance' => 'CREDIT']
        );

        // Credit: Net Accounts Payable to Vendor
        $journalLines[] = new JournalLineData(
            accountId: $apVendorAcc->id,
            debit: '0.0000',
            credit: $netPayable,
            memo: "Net AP payable to {$vendor->name} on {$bill->bill_number}"
        );

        // Credit: BIR Form 2307 EWT Withheld
        if (bccomp($ewtAmount, '0.0000', 4) > 0) {
            $journalLines[] = new JournalLineData(
                accountId: $ewtPayableAcc->id,
                debit: '0.0000',
                credit: $ewtAmount,
                memo: "BIR 2307 EWT withheld at source on {$bill->bill_number}"
            );
        }

        $entryData = new JournalEntryData(
            referenceNumber: 'JE-AP-' . $bill->bill_number,
            entryDate: $billDate,
            description: "AP accrual & EWT deduction for {$bill->bill_number} ({$vendor->name})",
            type: 'GENERAL',
            postedBy: auth()->id(),
            lines: $journalLines
        );

        $this->journalEntryService->createAndPostEntry($entryData);
    }
}
