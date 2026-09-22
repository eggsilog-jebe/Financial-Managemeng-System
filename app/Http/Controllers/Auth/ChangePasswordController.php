<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

final class ChangePasswordController extends Controller
{
    /** Show the forced change-password form. */
    public function show(): View
    {
        return view('auth.change-password');
    }

    /** Handle the password change submission. */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->mixedCase()],
        ]);

        $user = $request->user();

        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        $targetRoute = match ($user->role ?? 'StaffAccountant') {
            'Cashier' => route('collection.cashier-desk'),
            default   => route('accounting.dashboard'),
        };

        return redirect($targetRoute)
            ->with('success', 'Your password has been changed successfully. Welcome to the system!');
    }
}
