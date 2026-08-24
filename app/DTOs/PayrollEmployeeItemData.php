<?php

declare(strict_types=1);

namespace App\DTOs;

readonly final class PayrollEmployeeItemData
{
    public function __construct(
        public string $employeeIdNumber,
        public string $employeeName,
        public string $department, // NURSING, PHARMACY, LABORATORY, ADMIN, DOCTORS
        public string $basicSalary,
        public string $overtimePay = '0.0000',
        public string $allowances = '0.0000',
        public ?string $tin = null,
        public ?string $sssNumber = null,
        public ?string $philhealthNumber = null,
        public ?string $pagibigNumber = null,
        public ?string $bankAccountNumber = null,
    ) {}

    public function getGrossPay(): string
    {
        return bcadd(bcadd($this->basicSalary, $this->overtimePay, 4), $this->allowances, 4);
    }
}
