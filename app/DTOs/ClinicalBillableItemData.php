<?php

declare(strict_types=1);

namespace App\DTOs;

readonly final class ClinicalBillableItemData
{
    public function __construct(
        public string $itemCode,
        public string $description,
        public string $department, // LIS, RIS, PHARMACY, ROOM, SURGERY
        public string $revenueCategory, // CLINICAL, PHARMACY, ROOM, DOCTOR_FEE
        public string $quantity,
        public string $unitPrice,
        public bool $isVatable = true,
        public bool $isSeniorPwdEligible = true,
    ) {}

    public function getGrossAmount(): string
    {
        return bcmul($this->quantity, $this->unitPrice, 4);
    }
}
