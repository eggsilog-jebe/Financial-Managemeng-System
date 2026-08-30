<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Invoice;
use App\Models\PatientAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SimulateEncounterBillingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_ingest_encounter_billing_via_api(): void
    {
        $this->seed(\Database\Seeders\ChartOfAccountsSeeder::class);

        $payload = [
            'patient_name'      => 'Postman Test Patient',
            'admission_type'    => 'INPATIENT',
            'discount_category' => 'SENIOR_CITIZEN',
            'id_card_number'    => 'OSCA-2026-999',
            'hmo_provider'      => 'Maxicare Healthcare Corp',
            'philhealth_amount' => 15000.00,
            'hmo_limit'         => 30000.00,
            'items'             => [
                ['department' => 'ROOM_AND_BOARD', 'description' => 'Inpatient Room (3 Days)', 'quantity' => 3, 'unit_price' => 3500.00],
                ['department' => 'PHARMACY', 'description' => 'IV Medications Package', 'quantity' => 1, 'unit_price' => 4500.00],
            ],
        ];

        $response = $this->withHeaders([
            'X-Idempotency-Key' => 'POSTMAN-KEY-' . uniqid(),
            'Accept'            => 'application/json',
        ])->postJson('/api/v1/ingest/encounter-billing', $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.patient_name', 'Postman Test Patient');

        $this->assertDatabaseHas('patient_accounts', [
            'full_name' => 'Postman Test Patient',
            'hmo_provider' => 'Maxicare Healthcare Corp',
        ]);
    }
}
