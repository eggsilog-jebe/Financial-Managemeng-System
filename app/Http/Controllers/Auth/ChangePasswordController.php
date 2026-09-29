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
    public function update(Request $request): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // If voluntary change password from account settings (not forced), verify current password
        $rules = [
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->mixedCase()],
        ];

        if (!$user->must_change_password) {
            $rules['current_password'] = ['required', 'string'];
        }

        $request->validate($rules);

        if (!$user->must_change_password) {
            if (!Hash::check((string) $request->input('current_password'), (string) $user->password)) {
                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The provided current password does not match our records.',
                        'errors'  => [
                            'current_password' => ['The provided current password does not match our records.'],
                        ],
                    ], 422);
                }

                return back()->withErrors([
                    'current_password' => 'The provided current password does not match our records.',
                ]);
            }
        }

        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your password has been changed successfully.',
            ]);
        }

        $targetRoute = match ($user->role ?? 'StaffAccountant') {
            'Cashier' => route('collection.cashier-desk'),
            default   => route('accounting.dashboard'),
        };

        return redirect($targetRoute)
            ->with('success', 'Your password has been changed successfully. Welcome to the system!');
    }
}
