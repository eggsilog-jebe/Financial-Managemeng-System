<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\DTOs\ClinicalBillableItemData;
use App\DTOs\PatientBillingIngestionData;
use App\DTOs\PaymentReceiptData;
use App\DTOs\PurchaseBillItemData;
use App\DTOs\VendorBillIngestionData;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\CashierShift;
use App\Models\Invoice;
use App\Models\PatientAccount;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Accounting\AccountsPayableService;
use App\Services\Accounting\BillingIngestionService;
use App\Services\Accounting\CollectionService;
use Illuminate\Database\Seeder;

final class HospitalFinancialMockSeeder extends Seeder
{
    public function run(
        BillingIngestionService $billingService,
        AccountsPayableService $apService,
        CollectionService $collectionService
    ): void
    {
        // 1. Ensure Standard Chart of Accounts is Seeded
        $this->call(PhilippineHealthcareChartOfAccountsSeeder::class);

        // 2. Ensure Default Cashier Shift & Cashier User
        $cashierUser = User::firstOrCreate(
            ['email' => 'cashier@hospital.ph'],
            ['name' => 'Maria Santos (Senior Cashier)', 'password' => bcrypt('password'), 'role' => 'Cashier']
        );

        $shift = CashierShift::firstOrCreate(
            ['shift_code' => 'SHIFT-20260824-001'],
            [
                'cashier_id'         => $cashierUser->id,
                'opened_at'          => now()->startOfDay(),
                'opening_cash_float' => 5000.00,
                'status'             => 'OPEN',
            ]
        );

        // 3. Ensure Operating Bank Account
        $bank = BankAccount::firstOrCreate(
            ['account_number' => '1020-METRO-001'],
            [
                'name'           => 'Metrobank Hospital Operating Account',
                'bank_name'      => 'Metrobank - Medical Center Branch',
                'gl_code'        => '1020',
                'purpose'        => 'Operating',
                'currency'       => 'PHP',
                'balance'        => 500000.00,
                'status'         => 'Active',
            ]
        );

        // =========================================================================
        // PART A: SEED 10 REALISTIC PATIENT BILLS (AR) WITH STATUTORY DEDUCTIONS
        // =========================================================================
        $patientsData = [
            [
                'name' => 'Juan Dela Cruz', 'type' => 'Inpatient', 'hmo' => 'Maxicare', 'disc' => 'SENIOR_CITIZEN', 'id' => 'OSCA-MNL-1029',
                'lines' => [
                    ['code' => 'ROOM-PVT', 'desc' => 'Private Room Accommodation (3 Nights)', 'dept' => 'NURSING_WARDS', 'cat' => 'ROOM', 'qty' => 3, 'price' => 3500.00, 'vat' => false, 'snr' => true],
                    ['code' => 'MED-ANTIBIO', 'desc' => 'Meropenem 1g IV Infusion', 'dept' => 'PHARMACY', 'cat' => 'MEDICINE', 'qty' => 6, 'price' => 2200.00, 'vat' => true, 'snr' => true],
                    ['code' => 'LAB-CBC', 'desc' => 'Complete Blood Count with Platelet', 'dept' => 'LIS', 'cat' => 'LABORATORY', 'qty' => 2, 'price' => 650.00, 'vat' => false, 'snr' => true],
                ],
                'phic_code' => '99201', 'phic_amt' => '5600.0000', 'hmo_limit' => '10000.0000',
            ],
            [
                'name' => 'Corazon Aquino-Reyes', 'type' => 'Inpatient', 'hmo' => null, 'disc' => 'SENIOR_CITIZEN', 'id' => 'OSCA-QC-5541',
                'lines' => [
                    ['code' => 'SURG-APP', 'desc' => 'Laparoscopic Appendectomy Suite Fee', 'dept' => 'SURGERY', 'cat' => 'SURGERY', 'qty' => 1, 'price' => 38000.00, 'vat' => true, 'snr' => true],
                    ['code' => 'MED-ANES', 'desc' => 'Propofol Anesthesia Ampules', 'dept' => 'PHARMACY', 'cat' => 'MEDICINE', 'qty' => 3, 'price' => 1500.00, 'vat' => true, 'snr' => true],
                ],
                'phic_code' => '44970', 'phic_amt' => '18000.0000', 'hmo_limit' => '0.0000',
            ],
            [
                'name' => 'Rodrigo Duterte-Tan', 'type' => 'Inpatient', 'hmo' => 'Intellicare', 'disc' => 'PWD', 'id' => 'PWD-DVO-9921',
                'lines' => [
                    ['code' => 'ICU-DAY', 'desc' => 'Intensive Care Unit (ICU) Room Stay', 'dept' => 'NURSING_WARDS', 'cat' => 'ROOM', 'qty' => 2, 'price' => 8500.00, 'vat' => false, 'snr' => true],
                    ['code' => 'RAD-CT', 'desc' => 'CT Scan Whole Abdomen with Contrast', 'dept' => 'RIS', 'cat' => 'RADIOLOGY', 'qty' => 1, 'price' => 9800.00, 'vat' => false, 'snr' => true],
                ],
                'phic_code' => '74177', 'phic_amt' => '7200.0000', 'hmo_limit' => '12000.0000',
            ],
            [
                'name' => 'Leni Robredo-Santos', 'type' => 'Outpatient', 'hmo' => 'Medicard', 'disc' => null, 'id' => null,
                'lines' => [
                    ['code' => 'OPD-CONS', 'desc' => 'Executive Specialty Outpatient Consultation', 'dept' => 'OPD', 'cat' => 'CLINICAL', 'qty' => 1, 'price' => 1800.00, 'vat' => true, 'snr' => false],
                    ['code' => 'LAB-LIPID', 'desc' => 'Lipid Profile Comprehensive Panel', 'dept' => 'LIS', 'cat' => 'LABORATORY', 'qty' => 1, 'price' => 1400.00, 'vat' => true, 'snr' => false],
                ],
                'phic_code' => null, 'phic_amt' => '0.0000', 'hmo_limit' => '3200.0000',
            ],
            [
                'name' => 'Manuel Roxas Jr.', 'type' => 'Inpatient', 'hmo' => 'PhilCare', 'disc' => 'SENIOR_CITIZEN', 'id' => 'OSCA-MAK-8812',
                'lines' => [
                    ['code' => 'ROOM-SEMI', 'desc' => 'Semi-Private Room Accommodation', 'dept' => 'NURSING_WARDS', 'cat' => 'ROOM', 'qty' => 4, 'price' => 2200.00, 'vat' => false, 'snr' => true],
                    ['code' => 'MED-DIAL', 'desc' => 'Hemodialysis Supplies & Dialyzer Kit', 'dept' => 'PHARMACY', 'cat' => 'MEDICINE', 'qty' => 2, 'price' => 4500.00, 'vat' => true, 'snr' => true],
                ],
                'phic_code' => '90935', 'phic_amt' => '5200.0000', 'hmo_limit' => '8000.0000',
            ],
            [
                'name' => 'Grace Poe-Llamanzares', 'type' => 'Outpatient', 'hmo' => null, 'disc' => 'PWD', 'id' => 'PWD-QC-4412',
                'lines' => [
                    ['code' => 'RAD-XRAY', 'desc' => 'Chest X-Ray PA and Lateral View', 'dept' => 'RIS', 'cat' => 'RADIOLOGY', 'qty' => 1, 'price' => 1200.00, 'vat' => false, 'snr' => true],
                    ['code' => 'LAB-URINE', 'desc' => 'Automated Routine Urinalysis', 'dept' => 'LIS', 'cat' => 'LABORATORY', 'qty' => 1, 'price' => 450.00, 'vat' => false, 'snr' => true],
                ],
                'phic_code' => null, 'phic_amt' => '0.0000', 'hmo_limit' => '0.0000',
            ],
            [
                'name' => 'Ferdinand Marcos-Romualdez', 'type' => 'Inpatient', 'hmo' => 'Maxicare', 'disc' => null, 'id' => null,
                'lines' => [
                    ['code' => 'ROOM-STE', 'desc' => 'Presidential Hospital Suite (2 Nights)', 'dept' => 'NURSING_WARDS', 'cat' => 'ROOM', 'qty' => 2, 'price' => 15000.00, 'vat' => true, 'snr' => false],
                    ['code' => 'RAD-MRI', 'desc' => 'MRI Brain with Contrast 3.0T', 'dept' => 'RIS', 'cat' => 'RADIOLOGY', 'qty' => 1, 'price' => 22000.00, 'vat' => true, 'snr' => false],
                ],
                'phic_code' => '70553', 'phic_amt' => '9000.0000', 'hmo_limit' => '30000.0000',
            ],
            [
                'name' => 'Sara Carpio-Duterte', 'type' => 'Outpatient', 'hmo' => 'Medicard', 'disc' => null, 'id' => null,
                'lines' => [
                    ['code' => 'LAB-COVID', 'desc' => 'RT-PCR SARS-CoV-2 Molecular Test', 'dept' => 'LIS', 'cat' => 'LABORATORY', 'qty' => 1, 'price' => 2800.00, 'vat' => false, 'snr' => false],
                ],
                'phic_code' => null, 'phic_amt' => '0.0000', 'hmo_limit' => '2800.0000',
            ],
            [
                'name' => 'Panfilo Lacson Sr.', 'type' => 'Inpatient', 'hmo' => null, 'disc' => 'SENIOR_CITIZEN', 'id' => 'OSCA-CAV-3319',
                'lines' => [
                    ['code' => 'SURG-ORTHO', 'desc' => 'Total Knee Arthroplasty Surgical Fee', 'dept' => 'SURGERY', 'cat' => 'SURGERY', 'qty' => 1, 'price' => 65000.00, 'vat' => true, 'snr' => true],
                    ['code' => 'MED-IMPLANT', 'desc' => 'Titanium Joint Implant Prosthesis', 'dept' => 'PHARMACY', 'cat' => 'MEDICINE', 'qty' => 1, 'price' => 45000.00, 'vat' => true, 'snr' => true],
                ],
                'phic_code' => '27447', 'phic_amt' => '35000.0000', 'hmo_limit' => '0.0000',
            ],
            [
                'name' => 'Miriam Defensor-Santiago', 'type' => 'Inpatient', 'hmo' => 'Intellicare', 'disc' => 'SENIOR_CITIZEN', 'id' => 'OSCA-ILO-7721',
                'lines' => [
                    ['code' => 'ROOM-PVT', 'desc' => 'Private Deluxe Room Accommodation', 'dept' => 'NURSING_WARDS', 'cat' => 'ROOM', 'qty' => 5, 'price' => 4000.00, 'vat' => false, 'snr' => true],
                    ['code' => 'MED-CHEMO', 'desc' => 'Chemotherapy Infusion Medication', 'dept' => 'PHARMACY', 'cat' => 'MEDICINE', 'qty' => 2, 'price' => 18000.00, 'vat' => true, 'snr' => true],
                ],
                'phic_code' => '96413', 'phic_amt' => '14000.0000', 'hmo_limit' => '25000.0000',
            ],
        ];

        $createdInvoices = [];

        foreach ($patientsData as $pData) {
            $patient = PatientAccount::create([
                'patient_id_number' => 'PAT-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3))),
                'full_name'         => $pData['name'],
                'admission_type'    => $pData['type'],
                'hmo_provider'      => $pData['hmo'],
                'total_billed'      => '0.0000',
                'current_balance'   => '0.0000',
                'status'            => 'Active',
            ]);

            $billableItems = array_map(
                fn ($line) => new ClinicalBillableItemData(
                    itemCode: $line['code'],
                    description: $line['desc'],
                    department: $line['dept'],
                    revenueCategory: $line['cat'],
                    quantity: (string) $line['qty'],
                    unitPrice: (string) $line['price'],
                    isVatable: $line['vat'],
                    isSeniorPwdEligible: $line['snr']
                ),
                $pData['lines']
            );

            $dto = new PatientBillingIngestionData(
                patientAccountId: $patient->id,
                invoiceDate: date('Y-m-d'),
                items: $billableItems,
                discountType: $pData['disc'],
                idCardNumber: $pData['id'],
                philhealthMemberPin: $pData['phic_code'] ? '12-345678901-2' : null,
                philhealthPrimaryIcd: $pData['phic_code'] ? 'ICD-10-A01' : null,
                philhealthPrimaryCaseCode: $pData['phic_code'] ?? 'GENERAL',
                philhealthPrimaryCaseRateAmount: $pData['phic_amt'],
                philhealthSecondaryCaseCode: null,
                philhealthSecondaryCaseRateAmount: '0.0000',
                hmoProvider: $pData['hmo'],
                hmoLoaNumber: $pData['hmo'] ? 'LOA-' . rand(10000, 99999) : null,
                hmoCardNumber: $pData['hmo'] ? 'CARD-' . rand(100000, 999999) : null,
                hmoApprovedLimit: $pData['hmo_limit'],
            );

            $invoice = $billingService->ingestAndPostPatientBill($dto);
            $createdInvoices[] = $invoice;
        }

        // =========================================================================
        // PART B: SEED 5 PHARMACEUTICAL & EQUIPMENT PURCHASE BILLS (AP & BIR 2307)
        // =========================================================================
        $vendorsData = [
            [
                'name' => 'Zuellig Pharma Corporation', 'tin' => '000-123-456-000', 'ewt_rate' => 0.01, 'atc' => 'WI158',
                'items' => [
                    ['code' => 'DRUG-PARACET', 'desc' => 'IV Paracetamol 10mg/mL 100mL Vials (Batch of 500)', 'type' => 'GOODS_INVENTORY', 'qty' => 500, 'price' => 120.00],
                    ['code' => 'DRUG-CEFTRIAX', 'desc' => 'Ceftriaxone 1g Injectable Vials (Batch of 300)', 'type' => 'GOODS_INVENTORY', 'qty' => 300, 'price' => 280.00],
                ],
            ],
            [
                'name' => 'Metro Drug Inc. Philippines', 'tin' => '001-234-567-000', 'ewt_rate' => 0.01, 'atc' => 'WI158',
                'items' => [
                    ['code' => 'SUP-SYRINGES', 'desc' => 'Sterile Luer-Lok 5mL Syringes with Needle (Box of 1000)', 'type' => 'GOODS_INVENTORY', 'qty' => 50, 'price' => 850.00],
                    ['code' => 'SUP-IVCATH', 'desc' => 'IV Cannula / Catheter Gauge 20 (Box of 200)', 'type' => 'GOODS_INVENTORY', 'qty' => 40, 'price' => 1200.00],
                ],
            ],
            [
                'name' => 'GE Healthcare Diagnostics PH', 'tin' => '002-345-678-000', 'ewt_rate' => 0.02, 'atc' => 'WI160',
                'items' => [
                    ['code' => 'SRV-CTMAINT', 'desc' => 'Quarterly Precision Calibration & Tube Maintenance - CT Suite', 'type' => 'SERVICES_MAINTENANCE', 'qty' => 1, 'price' => 45000.00],
                ],
            ],
            [
                'name' => 'Siemens Medical Solutions PH', 'tin' => '003-456-789-000', 'ewt_rate' => 0.02, 'atc' => 'WI160',
                'items' => [
                    ['code' => 'SRV-MRIMAINT', 'desc' => 'Liquid Helium Cryogen Topping & Magnet Preventive Service', 'type' => 'SERVICES_MAINTENANCE', 'qty' => 1, 'price' => 75000.00],
                ],
            ],
            [
                'name' => 'B. Braun Medical Supplies Philippines', 'tin' => '004-567-890-000', 'ewt_rate' => 0.01, 'atc' => 'WI158',
                'items' => [
                    ['code' => 'SUP-IVFLUID', 'desc' => 'Plain 0.9% Sodium Chloride 1L Infusion Bags (Carton of 20)', 'type' => 'GOODS_INVENTORY', 'qty' => 150, 'price' => 650.00],
                    ['code' => 'SUP-INFUSION', 'desc' => 'Standard Blood Transfusion Infusion Sets (Box of 100)', 'type' => 'GOODS_INVENTORY', 'qty' => 30, 'price' => 1400.00],
                ],
            ],
        ];

        foreach ($vendorsData as $vIdx => $vData) {
            $code = 'VEND-00' . ($vIdx + 1);
            $vendor = Vendor::firstOrCreate(
                ['code' => $code],
                [
                    'tin'    => $vData['tin'],
                    'name'   => $vData['name'],
                    'status' => 'Active',
                ]
            );

            $billItems = array_map(
                fn ($i) => new PurchaseBillItemData(
                    itemCode: $i['code'],
                    description: $i['desc'],
                    expenseType: $i['type'],
                    quantity: (string) $i['qty'],
                    unitPrice: (string) $i['price'],
                    atcCode: $vData['atc']
                ),
                $vData['items']
            );

            $totalAmount = array_reduce(
                $vData['items'],
                fn ($carry, $i) => bcadd($carry, bcmul((string) $i['qty'], (string) $i['price'], 4), 4),
                '0.0000'
            );

            $apDto = new VendorBillIngestionData(
                vendorId: $vendor->id,
                doctorId: null,
                billDate: date('Y-m-d'),
                dueDate: date('Y-m-d', strtotime('+30 days')),
                poNumber: 'PO-2026-' . (100 + $vIdx),
                grnNumber: 'GRN-2026-' . (100 + $vIdx),
                vendorInvoiceNumber: 'INV-' . strtoupper(bin2hex(random_bytes(3))),
                poAmount: $totalAmount,
                grnAmount: $totalAmount,
                items: $billItems
            );

            $apService->ingestVendorBillAndPostAP($apDto);
        }

        // =========================================================================
        // PART C: SETTLE 6 OF THE SEEDED PATIENT BILLS VIA CASHIER DESK (ORs)
        // =========================================================================
        $paymentChannels = ['CASH', 'CREDIT_CARD', 'GCASH', 'MAYA', 'CASH', 'CREDIT_CARD'];

        for ($i = 0; $i < 6; $i++) {
            /** @var Invoice $invoice */
            $invoice = $createdInvoices[$i];

            // Re-fetch to get accurate patient_payable
            $invoice->refresh();

            $payable = (string) $invoice->patient_payable;

            if (bccomp($payable, '0.0000', 4) > 0) {
                $method = $paymentChannels[$i];
                $ref = in_array($method, ['GCASH', 'MAYA', 'CREDIT_CARD']) ? 'TXN-' . strtoupper(bin2hex(random_bytes(4))) : null;

                $payDto = new PaymentReceiptData(
                    patientAccountId: $invoice->patient_account_id,
                    invoiceId: $invoice->id,
                    cashierShiftId: $shift->id,
                    amount: $payable,
                    paymentMethod: $method,
                    transactionChannelRef: $ref,
                    payorName: $invoice->patientAccount->full_name,
                    payorTin: null,
                    paymentDate: date('Y-m-d'),
                    paymentType: 'PATIENT_COPAY',
                );

                $collectionService->processCollection($payDto);
            }
        }
    }
}
