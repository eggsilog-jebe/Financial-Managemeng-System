<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_can_update_profile_names(): void
    {
        $user = User::factory()->create([
            'first_name'  => 'John',
            'middle_name' => 'A',
            'last_name'   => 'Doe',
            'name'        => 'John A Doe',
            'email'       => 'test.user@fms.hospital',
            'password'    => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->postJson(route('account.profile.update'), [
            'first_name'  => 'Zedrick',
            'middle_name' => 'Ganton',
            'last_name'   => 'De Monteverde',
            'email'       => 'test.user@fms.hospital',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'user'    => [
                    'name'        => 'Zedrick Ganton De Monteverde',
                    'first_name'  => 'Zedrick',
                    'middle_name' => 'Ganton',
                    'last_name'   => 'De Monteverde',
                    'email'       => 'test.user@fms.hospital',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id'          => $user->id,
            'first_name'  => 'Zedrick',
            'middle_name' => 'Ganton',
            'last_name'   => 'De Monteverde',
            'name'        => 'Zedrick Ganton De Monteverde',
        ]);
    }

    public function test_changing_email_requires_current_password(): void
    {
        $user = User::factory()->create([
            'email'    => 'original@fms.hospital',
            'password' => Hash::make('secret-password'),
        ]);

        // Attempt without current password
        $response = $this->actingAs($user)->postJson(route('account.profile.update'), [
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'email'       => 'newemail@fms.hospital',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        // Attempt with wrong password
        $wrongPassResponse = $this->actingAs($user)->postJson(route('account.profile.update'), [
            'first_name'       => 'John',
            'last_name'        => 'Doe',
            'email'            => 'newemail@fms.hospital',
            'current_password' => 'wrong-pass',
        ]);

        $wrongPassResponse->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        // Attempt with correct password
        $correctPassResponse = $this->actingAs($user)->postJson(route('account.profile.update'), [
            'first_name'       => 'John',
            'last_name'        => 'Doe',
            'email'            => 'newemail@fms.hospital',
            'current_password' => 'secret-password',
        ]);

        $correctPassResponse->assertOk();
        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'email' => 'newemail@fms.hospital',
        ]);
    }

    public function test_user_can_upload_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create();

        $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)->postJson(route('account.photo.update'), [
            'photo' => $file,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_user_can_upload_photo_via_base64(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create();

        // 1x1 transparent PNG data url
        $base64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($user)->postJson(route('account.photo.update'), [
            'photo_base64' => $base64,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->avatar_path);
    }
}
