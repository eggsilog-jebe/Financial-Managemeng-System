<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class PatientInvoiceCreateData
{
    /**
     * @param array<int, array{item_code: string, description: string, department?: string, revenue_category?: string, quantity: string|float, unit_price: string|float, is_vatable?: bool, is_senior_pwd_eligible?: bool}> $items
     */
    public function __construct(
        public int $patientAccountId,
        public string $invoiceDate,
        public ?string $dueDate = null,
        public array $items = [],
        public ?string $discountType = null, // SENIOR_CITIZEN, PWD, EMPLOYEE, CHARITY
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
        public ?string $invoiceNumber = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            patientAccountId: (int) $data['patient_account_id'],
            invoiceDate: (string) $data['invoice_date'],
            dueDate: ! empty($data['due_date']) ? (string) $data['due_date'] : null,
            items: (array) ($data['items'] ?? []),
            discountType: ! empty($data['discount_type']) ? (string) $data['discount_type'] : null,
            idCardNumber: ! empty($data['id_card_number']) ? (string) $data['id_card_number'] : null,
            philhealthMemberPin: ! empty($data['philhealth_member_pin']) ? (string) $data['philhealth_member_pin'] : null,
            philhealthPrimaryIcd: ! empty($data['philhealth_primary_icd']) ? (string) $data['philhealth_primary_icd'] : null,
            philhealthPrimaryCaseCode: ! empty($data['philhealth_primary_case_code']) ? (string) $data['philhealth_primary_case_code'] : null,
            philhealthPrimaryCaseRateAmount: ! empty($data['philhealth_primary_case_rate_amount']) ? (string) $data['philhealth_primary_case_rate_amount'] : '0.0000',
            philhealthSecondaryCaseCode: ! empty($data['philhealth_secondary_case_code']) ? (string) $data['philhealth_secondary_case_code'] : null,
            philhealthSecondaryCaseRateAmount: ! empty($data['philhealth_secondary_case_rate_amount']) ? (string) $data['philhealth_secondary_case_rate_amount'] : '0.0000',
            hmoProvider: ! empty($data['hmo_provider']) ? (string) $data['hmo_provider'] : null,
            hmoLoaNumber: ! empty($data['hmo_loa_number']) ? (string) $data['hmo_loa_number'] : null,
            hmoCardNumber: ! empty($data['hmo_card_number']) ? (string) $data['hmo_card_number'] : null,
            hmoApprovedLimit: ! empty($data['hmo_approved_limit']) ? (string) $data['hmo_approved_limit'] : '0.0000',
            invoiceNumber: ! empty($data['invoice_number']) ? (string) $data['invoice_number'] : null,
        );
    }
}
