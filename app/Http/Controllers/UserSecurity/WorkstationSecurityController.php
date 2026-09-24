<?php

declare(strict_types=1);

namespace App\Http\Controllers\UserSecurity;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActiveSession;
use App\Models\UserWorkstation;
use App\Services\Security\ActiveSessionManagerService;
use App\Services\Security\WorkstationBindingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Super Admin Management Panel for Workstation Binding & Active Sessions.
 *
 * Capabilities:
 * - View active sessions and terminate any session in real time.
 * - Approve / reject pending workstation authorization requests.
 * - Manage and revoke bound workstations (enforcing 1–3 workstation quota).
 * - Real-time JSON data endpoint for live UI polling.
 */
final class WorkstationSecurityController extends Controller
{
    public function __construct(
        private readonly WorkstationBindingService   $workstationService,
        private readonly ActiveSessionManagerService $sessionManager,
    ) {}

    /**
     * Render the Super Admin Workstation & Session Security Panel.
     */
    public function index(Request $request): View
    {
        $pendingWorkstations = $this->workstationService->getPendingRequests();
        $boundWorkstations   = $this->workstationService->getBoundWorkstations();
        $activeSessions      = $this->sessionManager->getActiveSessions();
        $users               = User::where('status', 'active')->orderBy('name')->get();

        $metrics = [
            'pending_count'    => $pendingWorkstations->count(),
            'approved_count'   => $boundWorkstations->where('status', UserWorkstation::STATUS_APPROVED)->count(),
            'revoked_count'    => $boundWorkstations->where('status', UserWorkstation::STATUS_REVOKED)->count(),
            'active_sessions'  => $activeSessions->count(),
            'max_per_user'     => UserWorkstation::MAX_PER_USER,
        ];

        return view('user-security.workstations', [
            'pendingWorkstations' => $pendingWorkstations,
            'boundWorkstations'   => $boundWorkstations,
            'activeSessions'      => $activeSessions,
            'users'               => $users,
            'metrics'             => $metrics,
        ]);
    }

    /**
     * Real-time polling endpoint for live dashboard updates.
     */
    public function pollData(): JsonResponse
    {
        $pending = $this->workstationService->getPendingRequests();
        $active  = $this->sessionManager->getActiveSessions();

        return response()->json([
            'pending_count' => $pending->count(),
            'active_count'  => $active->count(),
            'pending'       => $pending->map(fn (UserWorkstation $w) => [
                'id'               => $w->id,
                'user_name'        => $w->user?->name ?? 'Unknown',
                'user_email'       => $w->user?->email ?? '',
                'user_role'        => $w->user?->roleLabel() ?? '',
                'workstation_name' => $w->workstation_name,
                'platform'         => $w->platform,
                'browser'          => $w->browser,
                'ip_address'       => $w->ip_address,
                'requested_at'     => $w->updated_at->diffForHumans(),
                'approved_count'   => $w->user?->approvedWorkstations()->count() ?? 0,
            ]),
            'active_sessions' => $active->map(fn (UserActiveSession $s) => [
                'id'               => $s->id,
                'session_id'       => substr($s->session_id, 0, 16) . '...',
                'user_name'        => $s->user?->name ?? 'Unknown',
                'user_role'        => $s->user?->roleLabel() ?? '',
                'device_name'      => $s->device_name,
                'ip_address'       => $s->ip_address,
                'login_at'         => $s->login_at->format('M d, H:i'),
                'last_activity'    => $s->last_activity_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * Super Admin: Approve a workstation authorization request.
     */
    public function approve(Request $request, UserWorkstation $workstation): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'workstation_name' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $this->workstationService->approveWorkstation(
                workstation: $workstation,
                admin:       Auth::user(),
                customName:  $validated['workstation_name'] ?? null
            );

            $message = "✅ Workstation [{$workstation->workstation_name}] approved successfully for {$workstation->user?->name}.";

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return back()->with('success', $message);
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withErrors($e->errors());
        }
    }

    /**
     * Super Admin: Reject a workstation authorization request.
     */
    public function reject(Request $request, UserWorkstation $workstation): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->workstationService->rejectWorkstation(
            workstation: $workstation,
            admin:       Auth::user(),
            reason:      $validated['rejection_reason'] ?? null
        );

        $message = "🚫 Workstation request for [{$workstation->user?->name}] was rejected.";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('warning', $message);
    }

    /**
     * Super Admin: Revoke an authorized workstation.
     */
    public function revoke(Request $request, UserWorkstation $workstation): RedirectResponse|JsonResponse
    {
        $this->workstationService->revokeWorkstation($workstation, Auth::user());

        $message = "⚠️ Workstation [{$workstation->workstation_name}] was revoked. All active sessions on this machine have been terminated.";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Super Admin: Force-terminate a live active session.
     */
    public function terminateSession(Request $request, UserActiveSession $session): RedirectResponse|JsonResponse
    {
        $this->sessionManager->terminateSession($session, Auth::user());

        $message = "🛑 Session for [{$session->user?->name}] was immediately terminated.";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
