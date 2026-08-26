<?php

declare(strict_types=1);

namespace App\DTOs\Accounting;

readonly final class AccountData
{
    public function __construct(
        public string $code,
        public string $name,
        public string $category,
        public string $normalBalance,
        public ?string $department = null,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): self
    {
        $category = strtoupper((string) ($data['category'] ?? $data['type'] ?? 'ASSET'));
        $normalBalance = strtoupper((string) ($data['normal_balance'] ?? (in_array($category, ['ASSET', 'EXPENSE'], true) ? 'DEBIT' : 'CREDIT')));

        return new self(
            code: trim((string) $data['code']),
            name: trim((string) $data['name']),
            category: $category,
            normalBalance: $normalBalance,
            department: ! empty($data['department']) ? trim((string) $data['department']) : null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : true,
        );
    }

    public function toArray(): array
    {
        return [
            'code'           => $this->code,
            'name'           => $this->name,
            'category'       => $this->category,
            'normal_balance' => $this->normalBalance,
            'department'     => $this->department,
            'is_active'      => $this->isActive,
        ];
    }
}
