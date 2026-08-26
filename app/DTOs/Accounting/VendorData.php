<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class VendorData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $tin = null,
        public ?string $contactPerson = null,
        public ?string $phone = null,
        public ?string $email = null,
        public int $paymentTermsDays = 30,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): self
    {
        $code = trim((string) ($data['code'] ?? $data['vendor_code'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));
        $tin = ! empty($data['tin']) ? trim((string) $data['tin']) : null;
        $contactPerson = ! empty($data['contact_person']) ? trim((string) $data['contact_person']) : null;
        $phone = ! empty($data['phone']) ? trim((string) $data['phone']) : null;
        $email = ! empty($data['email']) ? trim((string) $data['email']) : null;
        $paymentTerms = isset($data['payment_terms_days']) ? (int) $data['payment_terms_days'] : (isset($data['payment_terms']) ? (int) $data['payment_terms'] : 30);
        $isActive = isset($data['is_active']) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : (($data['status'] ?? 'Active') === 'Active');

        return new self(
            code: $code,
            name: $name,
            tin: $tin,
            contactPerson: $contactPerson,
            phone: $phone,
            email: $email,
            paymentTermsDays: $paymentTerms,
            isActive: $isActive,
        );
    }

    public function toArray(): array
    {
        return [
            'code'               => $this->code,
            'name'               => $this->name,
            'tin'                => $this->tin,
            'contact_person'     => $this->contactPerson,
            'phone'              => $this->phone,
            'email'              => $this->email,
            'payment_terms_days' => $this->paymentTermsDays,
            'status'             => $this->isActive ? 'Active' : 'Inactive',
        ];
    }
}
