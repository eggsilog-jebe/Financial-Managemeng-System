<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserWorkstation;
use App\Services\Security\ActiveSessionManagerService;
use App\Services\Security\WorkstationBindingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller handling the holding screen and real-time status polling
 * for unbound / unrecognized workstations awaiting Super Admin authorization.
 */
final class WorkstationAuthorizationController extends Controller
{
    public function __construct(
        private readonly WorkstationBindingService   $workstationService,
        private readonly ActiveSessionManagerService $sessionManager,
    ) {}

    /**
     * Display the Workstation Authorization Pending screen.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $deviceUuid = $this->workstationService->resolveDeviceUuid($request);

        /** @var UserWorkstation|null $workstation */
        $workstation = UserWorkstation::where('user_id', $user->id)
            ->where('device_uuid', $deviceUuid)
            ->first();

        // If no workstation record exists, submit one
        if (! $workstation) {
            $workstation = $this->workstationService->submitAuthorizationRequest($user, $deviceUuid, $request);
        }

        // If already approved, register active session and proceed to 2FA
        if ($workstation->isApproved()) {
            $this->sessionManager->registerSession($user, $request->session()->getId(), $workstation, $request);
            $request->session()->put('auth.workstation_id', $workstation->id);

            return redirect()->route(
                $user->hasTwoFactorEnabled() ? 'two-factor.challenge' : 'two-factor.setup'
            );
        }

        return view('auth.workstation-pending', [
            'user'        => $user,
            'workstation' => $workstation,
            'deviceUuid'  => $deviceUuid,
            'platform'    => $this->workstationService->detectPlatform($request->userAgent()),
            'browser'     => $this->workstationService->detectBrowser($request->userAgent()),
            'ip'          => $request->ip(),
        ]);
    }

    /**
     * Real-time polling endpoint (called via fetch every 2-3s from pending holding page).
     */
    public function checkStatus(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'status'       => 'unauthenticated',
                'redirect_url' => route('login'),
            ]);
        }

        $user = Auth::user();
        $deviceUuid = $this->workstationService->resolveDeviceUuid($request);

        /** @var UserWorkstation|null $workstation */
        $workstation = UserWorkstation::where('user_id', $user->id)
            ->where('device_uuid', $deviceUuid)
            ->first();

        if (! $workstation) {
            return response()->json([
                'status'  => 'unknown',
                'message' => 'No authorization request found.',
            ]);
        }

        if ($workstation->isApproved()) {
            // Register single active session upon approval
            $this->sessionManager->registerSession($user, $request->session()->getId(), $workstation, $request);
            $request->session()->put('auth.workstation_id', $workstation->id);

            $target = $user->hasTwoFactorEnabled()
                ? route('two-factor.challenge')
                : route('two-factor.setup');

            return response()->json([
                'status'       => 'approved',
                'redirect_url' => $target,
            ]);
        }

        if ($workstation->isRejected()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'status'       => 'rejected',
                'message'      => $workstation->rejection_reason ?: 'Workstation access was rejected by the Super Administrator.',
                'redirect_url' => route('login'),
            ]);
        }

        if ($workstation->isRevoked()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'status'       => 'revoked',
                'message'      => 'Workstation authorization was revoked.',
                'redirect_url' => route('login'),
            ]);
        }

        return response()->json([
            'status'     => 'pending',
            'updated_at' => $workstation->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Cancel the pending authorization request and log out.
     */
    public function cancel(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Workstation authorization request cancelled.');
    }
}
