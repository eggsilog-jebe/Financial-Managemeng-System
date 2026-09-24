<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorEnrollConfirmRequest;
use App\Models\ActivityLog;
use App\Services\Auth\TwoFactorAuthService;
use App\Services\Auth\TwoFactorRememberService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Manages TOTP (Google Authenticator) 2FA enrollment lifecycle:
 * - show()    → provision a TOTP secret & display the QR code for scanning
 * - store()   → re-provision a fresh QR code (called via fetch)
 * - confirm() → verify the first TOTP code to activate 2FA
 * - destroy() → disable 2FA for the authenticated user
 */
final class TwoFactorSetupController extends Controller
{
    public function __construct(
        private readonly TwoFactorAuthService     $twoFactorService,
        private readonly TwoFactorRememberService $twoFactorRememberService,
    ) {}

    /**
     * Provision a new TOTP secret and display the QR code enrollment page.
     * If the user already has a confirmed secret, redirect away.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('accounting.dashboard')
                ->with('info', 'Two-factor authentication is already enabled on your account.');
        }

        // Provision a fresh TOTP secret if the user doesn't have one yet
        if (! $user->two_factor_secret) {
            $this->twoFactorService->provision($user);
            $user->refresh();
        }

        $qrCodeSvg = $this->twoFactorService->getQrCodeSvg($user);

        return view('auth.two-factor-setup', [
            'qrCodeSvg' => $qrCodeSvg,
            'userEmail' => $user->email,
        ]);
    }

    /**
     * Re-provision a fresh QR/secret (called via JS fetch when user clicks "Regenerate").
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            return response()->json(['error' => '2FA is already active.'], 422);
        }

        $this->twoFactorService->provision($user);
        $user->refresh();

        ActivityLog::logAuth(
            event:       '2fa_totp_reprovisioned',
            user:        $user,
            description: "User [{$user->name}] regenerated their TOTP secret during enrollment.",
            ip:          $request->ip(),
            userAgent:   $request->userAgent(),
        );

        return response()->json([
            'qr_svg' => $this->twoFactorService->getQrCodeSvg($user),
        ]);
    }

    /**
     * Confirm TOTP enrollment by verifying the first code entered from Google Authenticator.
     */
    public function confirm(TwoFactorEnrollConfirmRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $this->twoFactorService->verifyCode($user, $request->input('code'))) {
            return redirect()->route('two-factor.setup')
                ->withErrors(['code' => 'The authenticator code is incorrect. Please wait for the next code and try again.']);
        }

        $this->twoFactorService->confirmEnrollment($user);

        // Mark 2FA as passed for this session immediately after enrollment
        $request->session()->put('auth.2fa_passed', true);
        $request->session()->put('auth.last_activity_at', now()->toIso8601String());

        $cookie = $this->twoFactorRememberService->forgetCookie($user);

        ActivityLog::logAuth(
            event:       '2fa_totp_enrolled',
            user:        $user,
            description: "User [{$user->name}] ({$user->role}) successfully enrolled in TOTP (Google Authenticator) 2FA.",
            ip:          $request->ip(),
            userAgent:   $request->userAgent(),
        );

        return redirect()->intended(
            match ($user->role ?? 'StaffAccountant') {
                'Cashier' => route('collection.cashier-desk'),
                default   => route('accounting.dashboard'),
            }
        )->withCookie($cookie)->with('success', '✅ Google Authenticator two-factor authentication has been enabled on your account.');
    }

    /**
     * Disable 2FA for the authenticated user.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $this->twoFactorService->disable($user);

        $request->session()->forget(['auth.2fa_passed', 'auth.2fa_expires_at']);
        $cookie = $this->twoFactorRememberService->forgetCookie($user);

        ActivityLog::logAuth(
            event:       '2fa_disabled',
            user:        $user,
            description: "User [{$user->name}] ({$user->role}) disabled TOTP Two-Factor Authentication.",
            ip:          $request->ip(),
            userAgent:   $request->userAgent(),
        );

        return redirect()->route('accounting.dashboard')
            ->withCookie($cookie)
            ->with('warning', '⚠️ Two-factor authentication has been disabled. Your account is now less secure.');
    }
}
