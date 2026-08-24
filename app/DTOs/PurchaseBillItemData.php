<?php

declare(strict_types=1);

namespace App\DTOs;

readonly final class PurchaseBillItemData
{
    public function __construct(
        public string $itemCode,
        public string $description,
        public string $expenseType, // GOODS_INVENTORY, SERVICES_MAINTENANCE, DOCTOR_PROFESSIONAL_FEE, CAPEX_EQUIPMENT, UTILITIES
        public string $quantity,
        public string $unitPrice,
        public string $atcCode = 'WI158', // WI158 (1%), WI160 (2%), WI010/WI020 (10%/15%)
    ) {}

    public function getGrossAmount(): string
    {
        return bcmul($this->quantity, $this->unitPrice, 4);
    }
}
