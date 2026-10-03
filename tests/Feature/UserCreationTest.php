<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cfo_can_create_a_new_personnel_user(): void
    {
        $cfo = User::factory()->create([
            'email' => 'cfo-test@hospital.gov.ph',
            'role' => 'CFO',
            'status' => 'active',
        ]);

        $response = $this->actingAs($cfo)->post('/user-security/users', [
            'name' => 'Maria Santos, CPA',
            'email' => 'maria.santos@hospital.gov.ph',
            'role' => 'StaffAccountant',
            'password' => 'Hospital2026!',
            'password_confirmation' => 'Hospital2026!',
        ]);

        $response->assertRedirect(route('user-security.users'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Maria Santos, CPA',
            'email' => 'maria.santos@hospital.gov.ph',
            'role' => 'StaffAccountant',
            'status' => 'active',
            'must_change_password' => true,
        ]);
    }

    public function test_creation_fails_when_password_confirmation_does_not_match(): void
    {
        $cfo = User::factory()->create(['role' => 'CFO']);

        $response = $this->actingAs($cfo)->post('/user-security/users', [
            'name' => 'Maria Santos, CPA',
            'email' => 'maria.santos@hospital.gov.ph',
            'role' => 'StaffAccountant',
            'password' => 'Hospital2026!',
            'password_confirmation' => 'Different123!',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', [
            'email' => 'maria.santos@hospital.gov.ph',
        ]);
    }

    public function test_creation_fails_when_role_is_empty(): void
    {
        $cfo = User::factory()->create(['role' => 'CFO']);

        $response = $this->actingAs($cfo)->post('/user-security/users', [
            'name' => 'Maria Santos, CPA',
            'email' => 'maria.santos@hospital.gov.ph',
            'role' => '',
            'password' => 'Hospital2026!',
            'password_confirmation' => 'Hospital2026!',
        ]);

        $response->assertSessionHasErrors('role');
    }

    public function test_creation_fails_when_password_has_no_digits(): void
    {
        $cfo = User::factory()->create(['role' => 'CFO']);

        $response = $this->actingAs($cfo)->post('/user-security/users', [
            'name' => 'Maria Santos, CPA',
            'email' => 'maria.santos@hospital.gov.ph',
            'role' => 'StaffAccountant',
            'password' => 'HospitalOnlyLetters!',
            'password_confirmation' => 'HospitalOnlyLetters!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_creation_fails_when_email_already_exists(): void
    {
        $cfo = User::factory()->create(['role' => 'CFO', 'email' => 'existing@hospital.gov.ph']);

        $response = $this->actingAs($cfo)->post('/user-security/users', [
            'name' => 'New User',
            'email' => 'existing@hospital.gov.ph',
            'role' => 'Cashier',
            'password' => 'Hospital2026!',
            'password_confirmation' => 'Hospital2026!',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'This email address is already registered to an existing hospital account.',
        ]);
    }

    public function test_email_and_name_are_trimmed_automatically(): void
    {
        $cfo = User::factory()->create(['role' => 'CFO']);

        $response = $this->actingAs($cfo)->post('/user-security/users', [
            'name' => '   Dr. Juan dela Cruz   ',
            'email' => '   JUAN.DELACRUZ@hospital.gov.ph   ',
            'role' => 'BillingClerk',
            'password' => 'Hospital2026!',
            'password_confirmation' => 'Hospital2026!',
        ]);

        $response->assertRedirect(route('user-security.users'));
        $this->assertDatabaseHas('users', [
            'name' => 'Dr. Juan dela Cruz',
            'email' => 'juan.delacruz@hospital.gov.ph',
        ]);
    }

    public function test_user_accounts_directory_paginates_at_five_users_per_page(): void
    {
        $cfo = User::factory()->create(['role' => 'CFO', 'email' => 'cfo-paginate@hospital.gov.ph']);

        // Create 7 additional users (total 8 users)
        User::factory()->count(7)->create();

        $response = $this->actingAs($cfo)->get(route('user-security.users'));

        $response->assertOk();
        $response->assertViewHas('users');

        $users = $response->viewData('users');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $users);
        $this->assertSame(5, $users->perPage());
        $this->assertCount(5, $users->items());
        $this->assertSame(8, $users->total());
        $this->assertTrue($users->hasPages());
    }
}
