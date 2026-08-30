<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class VendorData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $tin = null,
        public string $taxType = 'VAT_REGISTERED',
        public string $defaultEwtRate = '1.0000',
        public ?string $defaultAtcCode = 'WC158',
        public ?string $contactPerson = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $registeredAddress = null,
        public ?string $bankName = null,
        public ?string $bankAccountNumber = null,
        public ?string $bankAccountName = null,
        public int $paymentTermsDays = 30,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): self
    {
        $code = trim((string) ($data['code'] ?? $data['vendor_code'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));
        $tin = ! empty($data['tin']) ? trim((string) $data['tin']) : null;
        $taxType = ! empty($data['tax_type']) ? trim((string) $data['tax_type']) : 'VAT_REGISTERED';
        $defaultEwtRate = isset($data['default_ewt_rate']) ? (string) $data['default_ewt_rate'] : '1.0000';
        $defaultAtcCode = ! empty($data['default_atc_code']) ? trim((string) $data['default_atc_code']) : 'WC158';
        $contactPerson = ! empty($data['contact_person']) ? trim((string) $data['contact_person']) : null;
        $phone = ! empty($data['phone']) ? trim((string) $data['phone']) : null;
        $email = ! empty($data['email']) ? trim((string) $data['email']) : null;
        $registeredAddress = ! empty($data['registered_address']) ? trim((string) $data['registered_address']) : null;
        $bankName = ! empty($data['bank_name']) ? trim((string) $data['bank_name']) : null;
        $bankAccountNumber = ! empty($data['bank_account_number']) ? trim((string) $data['bank_account_number']) : null;
        $bankAccountName = ! empty($data['bank_account_name']) ? trim((string) $data['bank_account_name']) : null;
        $paymentTerms = isset($data['payment_terms_days']) ? (int) $data['payment_terms_days'] : (isset($data['payment_terms']) ? (int) $data['payment_terms'] : 30);
        $isActive = isset($data['is_active']) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : (($data['status'] ?? 'Active') === 'Active');

        return new self(
            code: $code,
            name: $name,
            tin: $tin,
            taxType: $taxType,
            defaultEwtRate: $defaultEwtRate,
            defaultAtcCode: $defaultAtcCode,
            contactPerson: $contactPerson,
            phone: $phone,
            email: $email,
            registeredAddress: $registeredAddress,
            bankName: $bankName,
            bankAccountNumber: $bankAccountNumber,
            bankAccountName: $bankAccountName,
            paymentTermsDays: $paymentTerms,
            isActive: $isActive,
        );
    }

    public function toArray(): array
    {
        return [
            'code'                => $this->code,
            'name'                => $this->name,
            'tin'                 => $this->tin,
            'tax_type'            => $this->taxType,
            'default_ewt_rate'    => $this->defaultEwtRate,
            'default_atc_code'    => $this->defaultAtcCode,
            'contact_person'      => $this->contactPerson,
            'phone'               => $this->phone,
            'email'               => $this->email,
            'registered_address'  => $this->registeredAddress,
            'bank_name'           => $this->bankName,
            'bank_account_number' => $this->bankAccountNumber,
            'bank_account_name'   => $this->bankAccountName,
            'payment_terms_days'  => $this->paymentTermsDays,
            'status'              => $this->isActive ? 'Active' : 'Inactive',
        ];
    }
}
