<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        $category = fake()->randomElement(['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE']);
        $normalBalance = in_array($category, ['ASSET', 'EXPENSE'], true) ? 'DEBIT' : 'CREDIT';

        return [
            'code'           => fake()->unique()->numerify('####'),
            'name'           => fake()->words(3, true) . ' Account',
            'category'       => $category,
            'normal_balance' => $normalBalance,
            'department'     => fake()->randomElement(['FINANCE', 'BILLING', 'PHARMACY', 'NURSING', 'ADMIN']),
            'is_active'      => true,
        ];
    }
}
