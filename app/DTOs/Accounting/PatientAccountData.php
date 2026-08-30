<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class PatientAccountData
{
    public function __construct(
        public string $patientMrn,
        public string $fullName,
        public ?string $dateOfBirth = null,
        public ?string $gender = null,
        public string $admissionType = 'Inpatient', // Inpatient, Outpatient, Emergency
        public string $discountCategory = 'NONE',
        public ?string $idCardNumber = null,
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
        $dob = ! empty($data['date_of_birth']) ? trim((string) $data['date_of_birth']) : null;
        $gender = ! empty($data['gender']) ? trim((string) $data['gender']) : null;
        $type = (string) ($data['admission_type'] ?? 'Inpatient');
        $discountCategory = ! empty($data['discount_category']) ? trim((string) $data['discount_category']) : 'NONE';
        $idCard = ! empty($data['id_card_number']) ? trim((string) $data['id_card_number']) : null;
        $hmo = ! empty($data['hmo_provider']) ? trim((string) $data['hmo_provider']) : null;
        $phone = ! empty($data['phone']) ? trim((string) $data['phone']) : null;
        $email = ! empty($data['email']) ? trim((string) $data['email']) : null;
        $address = ! empty($data['address']) ? trim((string) $data['address']) : null;
        $status = (string) ($data['status'] ?? 'Active');

        return new self(
            patientMrn: $mrn,
            fullName: $name,
            dateOfBirth: $dob,
            gender: $gender,
            admissionType: $type,
            discountCategory: $discountCategory,
            idCardNumber: $idCard,
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
            'date_of_birth'     => $this->dateOfBirth,
            'gender'            => $this->gender,
            'admission_type'    => $this->admissionType,
            'discount_category' => $this->discountCategory,
            'id_card_number'    => $this->idCardNumber,
            'hmo_provider'      => $this->hmoProvider,
            'phone'             => $this->phone,
            'email'             => $this->email,
            'address'           => $this->address,
            'status'            => $this->status,
        ];
    }
}
