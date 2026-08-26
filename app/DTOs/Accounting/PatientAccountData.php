<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class PatientAccountData
{
    public function __construct(
        public string $patientMrn,
        public string $fullName,
        public string $admissionType = 'Inpatient', // Inpatient, Outpatient, Emergency
        public ?string $hmoProvider = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $address = null,
        public string $status = 'Active',
    ) {}

    public static function fromArray(array $data): self
    {
        $mrn = trim((string) ($data['patient_mrn'] ?? $data['patient_id_number'] ?? ''));
        $name = trim((string) ($data['full_name'] ?? ''));
        $type = (string) ($data['admission_type'] ?? 'Inpatient');
        $hmo = ! empty($data['hmo_provider']) ? trim((string) $data['hmo_provider']) : null;
        $phone = ! empty($data['phone']) ? trim((string) $data['phone']) : null;
        $email = ! empty($data['email']) ? trim((string) $data['email']) : null;
        $address = ! empty($data['address']) ? trim((string) $data['address']) : null;
        $status = (string) ($data['status'] ?? 'Active');

        return new self(
            patientMrn: $mrn,
            fullName: $name,
            admissionType: $type,
            hmoProvider: $hmo,
            phone: $phone,
            email: $email,
            address: $address,
            status: $status,
        );
    }

    public function toArray(): array
    {
        return [
            'patient_id_number' => $this->patientMrn,
            'full_name'         => $this->fullName,
            'admission_type'    => $this->admissionType,
            'hmo_provider'      => $this->hmoProvider,
            'phone'             => $this->phone,
            'email'             => $this->email,
            'address'           => $this->address,
            'status'            => $this->status,
        ];
    }
}
