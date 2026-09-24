<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Set the currently logged in user for the application and mark 2FA as passed by default.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string|null  $driver
     * @return $this
     */
    public function actingAs($user, $driver = null)
    {
        $this->withSession([
            'auth.2fa_passed'       => true,
            'auth.last_activity_at' => now()->toIso8601String(),
        ]);

        return parent::actingAs($user, $driver);
    }
}
