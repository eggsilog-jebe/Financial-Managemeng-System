<?php

declare(strict_types=1);

namespace App\Http\Controllers\UserSecurity;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserSecurity\StoreUserRequest;
use App\Http\Requests\UserSecurity\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

final class UserManagementController extends Controller
{
    /** List all hospital system users. */
    public function index(Request $request): View
    {
        Gate::authorize('access-user-management');

        $search = $request->query('search');
        $role = $request->query('role');
        $status = $request->query('status');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $users = $query->orderBy('role')
            ->orderBy('name')
            ->paginate(5)
            ->withQueryString();

        $metrics = [
            'total'         => User::count(),
            'active'        => User::where('status', 'active')->count(),
            'suspended'     => User::where('status', 'suspended')->count(),
            'pending_reset' => User::where('must_change_password', true)->count(),
        ];

        return view('user-security.users.index', compact('users', 'metrics', 'search', 'role', 'status'));
    }

    /** Show the create user form. */
    public function create(): View
    {
        Gate::authorize('access-user-management');

        return view('user-security.users.create');
    }

    /** Store a new hospital system user. */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('access-user-management');

        $user = User::create([
            'name'                 => trim($request->name),
            'email'                => strtolower(trim($request->email)),
            'role'                 => $request->role,
            'password'             => Hash::make($request->password),
            'status'               => 'active',
            'must_change_password' => true, // Force password change on first login
        ]);

        return redirect()
            ->route('user-security.users')
            ->with('success', "Personnel user [{$user->name}] ({$user->email}) created successfully with role [{$user->roleLabel()}]. They must change their temporary password on initial login.");
    }

    /** Show the edit user form. */
    public function edit(User $user): View
    {
        Gate::authorize('access-user-management');

        return view('user-security.users.edit', compact('user'));
    }

    /** Update a hospital system user's profile and role. */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'name'  => $request->name,
            'email' => strtolower(trim($request->email)),
            'role'  => $request->role,
        ]);

        return redirect()
            ->route('user-security.users')
            ->with('success', "User [{$user->name}] updated successfully.");
    }

    /** Reset a user's password to a temporary password and force change on next login. */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('access-user-management');

        $temporaryPassword = 'Hospital@' . now()->year;

        $user->update([
            'password'             => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('user-security.users')
            ->with('success', "Password for [{$user->name}] has been reset. Temporary password: <code>{$temporaryPassword}</code>. They must change it on next login.");
    }

    /** Toggle a user's status between active and suspended. */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('access-user-management');

        // Prevent CFO from suspending themselves
        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('user-security.users')
                ->with('error', 'You cannot suspend your own account.');
        }

        $newStatus = $user->isActive() ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activated' : 'suspended';

        return redirect()
            ->route('user-security.users')
            ->with('success', "User [{$user->name}] has been {$label}.");
    }
}
