<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class PurchaseBillCreateData
{
    /**
     * @param array<int, array{item_code: string, description: string, expense_type: string, quantity: string|float, unit_price: string|float, atc_code?: string}> $items
     */
    public function __construct(
        public int $vendorId,
        public string $billDate,
        public string $dueDate,
        public string $poNumber,
        public string $grnNumber,
        public string $vendorInvoiceNumber,
        public string $poAmount,
        public string $grnAmount,
        public array $items,
        public ?int $doctorId = null,
        public ?string $billNumber = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            vendorId: (int) $data['vendor_id'],
            billDate: (string) $data['bill_date'],
            dueDate: (string) $data['due_date'],
            poNumber: (string) ($data['po_number'] ?? 'PO-' . date('Ymd')),
            grnNumber: (string) ($data['grn_number'] ?? 'GRN-' . date('Ymd')),
            vendorInvoiceNumber: (string) ($data['vendor_invoice_number'] ?? 'INV-' . strtoupper(bin2hex(random_bytes(3)))),
            poAmount: (string) ($data['po_amount'] ?? '0.0000'),
            grnAmount: (string) ($data['grn_amount'] ?? '0.0000'),
            items: (array) ($data['items'] ?? []),
            doctorId: ! empty($data['doctor_id']) ? (int) $data['doctor_id'] : null,
            billNumber: ! empty($data['bill_number']) ? (string) $data['bill_number'] : null,
        );
    }
}
