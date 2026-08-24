<?php

declare(strict_types=1);

namespace App\DTOs;

readonly final class PatientBillingIngestionData
{
    /**
     * @param array<ClinicalBillableItemData> $items
     */
    public function __construct(
        public int $patientAccountId,
        public string $invoiceDate,
        public array $items,
        public ?string $discountType = null, // SENIOR_CITIZEN, PWD, or null
        public ?string $idCardNumber = null,
        public ?string $philhealthMemberPin = null,
        public ?string $philhealthPrimaryIcd = null,
        public ?string $philhealthPrimaryCaseCode = null,
        public string $philhealthPrimaryCaseRateAmount = '0.0000',
        public ?string $philhealthSecondaryCaseCode = null,
        public string $philhealthSecondaryCaseRateAmount = '0.0000',
        public ?string $hmoProvider = null,
        public ?string $hmoLoaNumber = null,
        public ?string $hmoCardNumber = null,
        public string $hmoApprovedLimit = '0.0000',
    ) {}
}
