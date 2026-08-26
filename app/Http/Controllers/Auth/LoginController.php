<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LoginController extends Controller
{
    /**
     * Show the Login Screen with instant demo quick-login buttons.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember', true))) {
            $request->session()->regenerate();
            $request->session()->save();

            $user = Auth::user();

            // Redirect appropriately based on user role
            return match ($user->role ?? 'StaffAccountant') {
                'Cashier' => redirect()->intended(route('collection.cashier-desk')),
                default   => redirect()->intended(route('accounting.dashboard')),
            };
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our registered hospital records.',
        ])->onlyInput('email');
    }

    /**
     * Quick Demo Switcher - instant login without typing passwords.
     */
    public function quickLogin(string $role): RedirectResponse
    {
        $email = match (strtolower($role)) {
            'cfo'        => 'cfo@hospital.local',
            'cashier'    => 'cashier@hospital.local',
            'accountant' => 'accountant@hospital.local',
            'auditor'    => 'auditor@hospital.local',
            default      => 'cfo@hospital.local',
        };

        $user = \App\Models\User::where('email', $email)->first();

        if ($user) {
            Auth::login($user, true);
            request()->session()->regenerate();
            request()->session()->save();

            return match ($user->role) {
                'Cashier' => redirect()->route('collection.cashier-desk'),
                default   => redirect()->route('accounting.dashboard'),
            };
        }

        return redirect()->route('login');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
