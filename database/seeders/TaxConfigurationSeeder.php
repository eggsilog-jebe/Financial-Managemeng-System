<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TaxRule;
use Illuminate\Database\Seeder;

final class TaxConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'tax_code' => 'WC158',
                'name'     => 'EWT on Procurement of Goods (1%)',
                'atc_code' => 'WC158',
                'category' => 'WITHHOLDING_TAX',
                'cat_type' => 'EXPANDED',
                'rate'     => '0.0100',
                'scope'    => 'Philippine regular suppliers of hospital medicines, surgicals, and office goods.',
                'status'   => 'Active',
            ],
            [
                'tax_code' => 'WC160',
                'name'     => 'EWT on Medical & Technical Services (2%)',
                'atc_code' => 'WC160',
                'category' => 'WITHHOLDING_TAX',
                'cat_type' => 'EXPANDED',
                'rate'     => '0.0200',
                'scope'    => 'Contractors of bio-medical engineering, laundry, janitorial, and security services.',
                'status'   => 'Active',
            ],
            [
                'tax_code' => 'WI010',
                'name'     => 'EWT on Doctor Professional Fees (10%)',
                'atc_code' => 'WI010',
                'category' => 'WITHHOLDING_TAX',
                'cat_type' => 'EXPANDED',
                'rate'     => '0.1000',
                'scope'    => 'Consultant physicians and surgeons without sworn declaration (gross <= 3M).',
                'status'   => 'Active',
            ],
            [
                'tax_code' => 'WI020',
                'name'     => 'EWT on Doctor Professional Fees > 3M (15%)',
                'atc_code' => 'WI020',
                'category' => 'WITHHOLDING_TAX',
                'cat_type' => 'EXPANDED',
                'rate'     => '0.1500',
                'scope'    => 'Consultant physicians and surgeons exceeding ?3,000,000.00 annual gross threshold.',
                'status'   => 'Active',
            ],
            [
                'tax_code' => 'WI158',
                'name'     => 'EWT on Top Withholding Agent (TWA) Purchases (1%)',
                'atc_code' => 'WI158',
                'category' => 'WITHHOLDING_TAX',
                'cat_type' => 'EXPANDED',
                'rate'     => '0.0100',
                'scope'    => 'TWA hospital purchases from regular local suppliers.',
                'status'   => 'Active',
            ],
            [
                'tax_code' => 'VAT-12',
                'name'     => 'Standard Value-Added Tax (12%)',
                'atc_code' => 'VAT-12',
                'category' => 'VAT',
                'cat_type' => 'OUTPUT_VAT',
                'rate'     => '12.0000',
                'scope'    => 'Non-hospital commercial transactions, parking, retail pharmacy sales to non-patients.',
                'status'   => 'Active',
            ],
            [
                'tax_code' => 'VAT-EXEMPT',
                'name'     => 'NIRC Section 109 Healthcare VAT Exemption',
                'atc_code' => 'VAT-EXEMPT',
                'category' => 'VAT',
                'cat_type' => 'EXEMPT',
                'rate'     => '0.0000',
                'scope'    => 'Inpatient and outpatient medical, dental, hospital, and nursing services.',
                'status'   => 'Active',
            ],
        ];

        foreach ($rules as $r) {
            TaxRule::updateOrCreate(['tax_code' => $r['tax_code']], $r);
        }
    }
}
