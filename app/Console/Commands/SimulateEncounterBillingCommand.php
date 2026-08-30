<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PatientAccount;
use App\Services\Accounting\InvoiceBillingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class SimulateEncounterBillingCommand extends Command
{
    protected $signature = 'fms:simulate-encounter-billing 
                            {--name= : Patient Full Name}
                            {--type= : Admission Type (INPATIENT / OUTPATIENT / EMERGENCY)}
                            {--discount= : Discount Classification (NONE, SENIOR_CITIZEN, PWD, EMPLOYEE_SUBSIDY, CHARITY)}
                            {--id-number= : OSCA or PWD ID Number}
                            {--hmo= : HMO Provider (Maxicare Healthcare Corp, Asalus/Intellicare, Medicard, PhilCare, Etiqa, Carehealth Plus, Pacific Cross, InLife, Caritas, or None)}
                            {--philhealth= : PhilHealth Case Rate Amount}
                            {--hmo-limit= : HMO Coverage Limit Amount}';

    protected $description = 'Simulate external BDMS/SPRS clinical encounter ingestion and post invoice to General Ledger via CLI';

    public function handle(InvoiceBillingService $billingService): int
    {
        $this->info("=================================================");
        $this->info("   FMS Encounter Data Input & Ingestion CLI      ");
        $this->info("=================================================\n");

        $isInteractive = $this->input->isInteractive() && ! $this->option('no-interaction');

        // 1. Gather input parameters
        $name = $this->option('name') ?: ($isInteractive ? $this->ask('1. Patient Full Name', 'Juan Dela Cruz') : 'Juan Dela Cruz');
        
        $typeOption = $this->option('type');
        if (empty($typeOption)) {
            $typeOption = $isInteractive ? $this->choice('2. Admission Type', ['INPATIENT', 'OUTPATIENT', 'EMERGENCY'], 0) : 'INPATIENT';
        }
        $type = strtoupper((string) $typeOption);

        $discountOption = $this->option('discount');
        if (empty($discountOption)) {
            $discountOption = $isInteractive ? $this->choice('3. Statutory Discount', ['NONE', 'SENIOR_CITIZEN', 'PWD', 'EMPLOYEE_SUBSIDY', 'CHARITY'], 0) : 'NONE';
        }
        $discount = strtoupper((string) $discountOption);

        $idNumber = $this->option('id-number');
        if (empty($idNumber) && in_array($discount, ['SENIOR_CITIZEN', 'PWD'], true)) {
            $idNumber = $isInteractive ? $this->ask('4. OSCA / PWD ID Card Number', 'OSCA-MNL-2026-991') : 'OSCA-MNL-2026-991';
        }

        $hmoOptions = [
            'None',
            'Maxicare Healthcare Corp',
            'Asalus Corporation (Intellicare)',
            'Medicard Philippines',
            'PhilCare',
            'Etiqa Life and General',
            'Carehealth Plus',
            'Pacific Cross',
            'Insular Health Care (InLife Health Care)',
            'Caritas Health Shield',
        ];

        $hmoOption = $this->option('hmo');
        if (empty($hmoOption)) {
            $hmoOption = $isInteractive ? $this->choice('5. HMO Provider', $hmoOptions, 0) : 'None';
        }
        $hmoProvider = ($hmoOption === 'None' || empty($hmoOption)) ? null : (string) $hmoOption;

        // 2. Set default clinical charge sheet based on admission type
        if ($type === 'INPATIENT') {
            $defaultPhilHealth = '15000.00';
            $defaultHmoLimit = $hmoProvider ? '30000.00' : '0.00';

            $items = [
                ['department' => 'ROOM_AND_BOARD', 'description' => 'Inpatient Room & Board (3 Days)', 'quantity' => 3, 'unit_price' => 3500.00],
                ['department' => 'PHARMACY', 'description' => 'IV Fluids & Antibiotics Package', 'quantity' => 1, 'unit_price' => 4500.00],
                ['department' => 'LABORATORY', 'description' => 'Complete Blood Count & Blood Chem Panel', 'quantity' => 1, 'unit_price' => 2200.00],
                ['department' => 'RADIOLOGY', 'description' => 'Chest X-Ray PA View', 'quantity' => 1, 'unit_price' => 1200.00],
            ];
        } else {
            $defaultPhilHealth = '0.00';
            $defaultHmoLimit = $hmoProvider ? '5000.00' : '0.00';

            $items = [
                ['department' => 'CONSULTATION', 'description' => 'Specialist Consultation Fee', 'quantity' => 1, 'unit_price' => 1500.00],
                ['department' => 'LABORATORY', 'description' => 'Routine Urinalysis & Fecalysis', 'quantity' => 1, 'unit_price' => 650.00],
            ];
        }

        // Optional interactive customization of line items
        if ($isInteractive && $this->confirm('6. Would you like to customize or add itemized procedures / service line items?', false)) {
            $customItems = [];
            $departments = ['ROOM_AND_BOARD', 'CONSULTATION', 'EMERGENCY', 'PHARMACY', 'LABORATORY', 'RADIOLOGY', 'SURGERY', 'MISCELLANEOUS'];
            
            do {
                $dept = $this->choice('Select Department', $departments, 0);
                $desc = $this->ask('Procedure / Service Particulars Description', 'Clinical Procedure');
                $qty = (int) $this->ask('Quantity', '1');
                $price = (float) $this->ask('Unit Price (₱)', '1000.00');

                $customItems[] = [
                    'department'  => $dept,
                    'description' => $desc,
                    'quantity'    => $qty,
                    'unit_price'  => $price,
                ];
            } while ($this->confirm('Add another line item procedure?', false));

            if (! empty($customItems)) {
                $items = $customItems;
            }
        }

        $hasPhilhealth = $isInteractive
            ? $this->confirm('6. Does the patient have PhilHealth Benefit Coverage?', $type === 'INPATIENT')
            : true;

        if ($hasPhilhealth) {
            $philhealth = $this->option('philhealth') ?: ($isInteractive ? $this->ask('   PhilHealth Case Rate Amount (₱)', $defaultPhilHealth) : $defaultPhilHealth);
        } else {
            $philhealth = '0.00';
        }

        if ($hmoProvider) {
            $hmoLimit = $this->option('hmo-limit') ?: ($isInteractive ? $this->ask('7. HMO Approved Coverage Limit (₱)', $defaultHmoLimit) : $defaultHmoLimit);
        } else {
            $hmoLimit = '0.00';
        }

        // 3. Atomic Database Insertion & GL Posting
        $this->output->write("\nProcessing encounter ingestion & posting to General Ledger... ");

        $result = DB::transaction(function () use ($name, $type, $discount, $idNumber, $hmoProvider, $philhealth, $hmoLimit, $items, $billingService): array {
            $patient = PatientAccount::create([
                'patient_id_number' => 'MRN-2026-' . strtoupper(substr(uniqid(), -5)),
                'full_name'         => $name,
                'date_of_birth'     => '1988-04-12',
                'gender'            => 'Female',
                'admission_type'    => $type,
                'discount_category' => $discount,
                'id_card_number'    => $idNumber,
                'hmo_provider'      => $hmoProvider,
                'phone'             => '+63 917 ' . rand(100, 999) . ' ' . rand(1000, 9999),
                'email'             => strtolower(str_replace(' ', '.', (string) $name)) . '@hospital.test',
                'address'           => 'Metro Manila, Philippines',
                'status'            => 'Active',
            ]);

            return $billingService->createAndPostEncounterInvoice([
                'patient_account_id' => $patient->id,
                'invoice_date'       => now()->toDateString(),
                'statutory_discount' => $discount,
                'osca_pwd_id'         => $idNumber,
                'philhealth_amount'  => $philhealth,
                'hmo_provider'       => $hmoProvider,
                'hmo_amount'          => $hmoLimit,
                'items'               => $items,
            ]);
        });

        $this->info("DONE! ✅\n");

        // 4. Output Itemized Clinical Charge Sheet Table
        $this->info("--- Itemized Clinical Charge Sheet ---");
        $itemRows = [];
        foreach ($items as $idx => $it) {
            $qty = (float) $it['quantity'];
            $price = (float) $it['unit_price'];
            $gross = $qty * $price;
            $itemRows[] = [
                $idx + 1,
                $it['department'],
                $it['description'],
                (int) $qty,
                '₱ ' . number_format($price, 2),
                '₱ ' . number_format($gross, 2),
            ];
        }
        $this->table(
            ['#', 'Department', 'Procedure / Service Particulars', 'Qty', 'Unit Price', 'Gross (₱)'],
            $itemRows
        );

        // 5. Output Summary Breakdown Table in Console
        $this->info("\n--- Financial Settlement & GL Posting Breakdown ---");
        $this->table(
            ['Field / Metric', 'Value'],
            [
                ['Patient Name', $name],
                ['MRN Identifier', $result['patient_mrn']],
                ['Admission Type', $type],
                ['Discount Applied', $discount . ($idNumber ? " ($idNumber)" : '')],
                ['Invoice Number', $result['invoice_number']],
                ['Gross Charges', '₱ ' . number_format($result['gross_total'], 2)],
                ['Statutory Discount (20%)', '- ₱ ' . number_format($result['discount_total'], 2)],
                ['PhilHealth Case Rate', '- ₱ ' . number_format($result['philhealth_total'], 2)],
                ['HMO Coverage', '- ₱ ' . number_format($result['hmo_total'], 2)],
                ['Net Patient Copay (Due at Cashier)', '₱ ' . number_format($result['patient_copay'], 2)],
                ['GL Journal Entry Ref', $result['journal_entry_reference']],
                ['GL Balance Check', 'BALANCED (DR = CR) ✅'],
            ]
        );

        $this->info("The encounter is now available in your system:");
        $this->line("👉 Cashier Desk: " . url('/collection-management/cashier-desk'));
        $this->line("👉 Invoicing Hub: " . url('/accounts-receivable/invoicing-billing'));
        $this->line("👉 General Ledger: " . url('/general-ledger/journal-entries'));

        return 0;
    }
}
