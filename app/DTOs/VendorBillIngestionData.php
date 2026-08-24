<?php

declare(strict_types=1);

namespace App\DTOs;

readonly final class VendorBillIngestionData
{
    /**
     * @param array<PurchaseBillItemData> $items
     */
    public function __construct(
        public int $vendorId,
        public ?int $doctorId,
        public string $billDate,
        public string $dueDate,
        public string $poNumber,
        public string $grnNumber,
        public string $vendorInvoiceNumber,
        public string $poAmount,
        public string $grnAmount,
        public array $items,
    ) {}
}
