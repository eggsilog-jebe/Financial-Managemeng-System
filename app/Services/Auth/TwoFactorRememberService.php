<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Throwable;

final class TwoFactorRememberService
{
    private const COOKIE_PREFIX = 'hims_2fa_remember_';

    /**
     * Issue an encrypted device cookie certifying this user has passed 2FA.
     * Note: 8-hour remember bypass is permanently disabled per hospital security policy;
     * clears any existing remember cookie instead.
     */
    public function issueCookie(User $user): SymfonyCookie
    {
        return $this->forgetCookie($user);
    }

    /**
     * Check whether the request has an active, untampered 2FA token for this user.
     * Note: 8-hour remember bypass is permanently disabled; verification is strictly required on every login.
     */
    public function isValid(Request $request, User $user): bool
    {
        return false;
    }

    /**
     * Invalidate the 2FA remember cookie on logout.
     */
    public function forgetCookie(User $user): SymfonyCookie
    {
        return Cookie::forget($this->cookieName($user->id));
    }

    /**
     * Cookie name scoped per user ID to prevent multi-account collisions.
     */
    public function cookieName(int|string $userId): string
    {
        return self::COOKIE_PREFIX . $userId;
    }

    /**
     * HMAC signature incorporating user ID, expiry, app key, and password hash
     * (so password reset/change automatically invalidates all existing 2FA cookies).
     */
    private function signature(User $user, int $expiresAt): string
    {
        $appKey = (string) config('app.key', '');
        $data   = "{$user->id}|{$expiresAt}|{$user->password}";

        return hash_hmac('sha256', $data, $appKey);
    }
}
