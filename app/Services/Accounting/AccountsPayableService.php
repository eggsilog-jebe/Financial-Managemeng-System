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
            $poAmount = property_exists($data, 'poAmount') ? (string) $data->poAmount : $totalGross;
            $grnAmount = property_exists($data, 'grnAmount') ? (string) $data->grnAmount : $totalGross;

            $priceVariance = bcsub($totalGross, $poAmount, 4);
            $receiptVariance = bcsub($totalGross, $grnAmount, 4);

            $matchStatus = 'MATCHED';
            if (bccomp($priceVariance, '0.0000', 4) !== 0) {
                $matchStatus = bccomp($priceVariance, '0.0000', 4) > 0 ? 'OVER_BILLED' : 'PRICE_MISMATCH';
            } elseif (bccomp($receiptVariance, '0.0000', 4) !== 0) {
                $matchStatus = 'QTY_MISMATCH';
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

            // 7. Post General Ledger AP Double-Entry Journal
            $this->postAPDoubleEntry($bill, $data->billDate, $vendor, $totalGross, $totalNetPayable, $totalEwt);

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

    private function postAPDoubleEntry(
        PurchaseBill $bill,
        string $billDate,
        Vendor $vendor,
        string $totalGross,
        string $netPayable,
        string $ewtAmount
    ): void {
        $inventoryExpenseAcc = Account::firstOrCreate(
            ['code' => '5020'],
            ['name' => 'Medical & Hospital Operating Supplies', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']
        );

        $apVendorAcc = Account::firstOrCreate(
            ['code' => '2010'],
            ['name' => 'Accounts Payable - Vendors & Suppliers', 'category' => 'LIABILITY', 'normal_balance' => 'CREDIT']
        );

        $ewtPayableAcc = Account::firstOrCreate(
            ['code' => '2030'],
            ['name' => 'Withholding Tax Payable - Expanded (BIR 2307)', 'category' => 'LIABILITY', 'normal_balance' => 'CREDIT']
        );

        $journalLines = [
            // Debit: Gross Expense / Inventory
            new JournalLineData(
                accountId: $inventoryExpenseAcc->id,
                debit: $totalGross,
                credit: '0.0000',
                memo: "Procurement invoice {$bill->bill_number} from {$vendor->name}"
            ),
            // Credit: Net Accounts Payable to Vendor
            new JournalLineData(
                accountId: $apVendorAcc->id,
                debit: '0.0000',
                credit: $netPayable,
                memo: "Net AP payable to {$vendor->name} on {$bill->bill_number}"
            ),
        ];

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
