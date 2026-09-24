<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserWorkstation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

/**
 * Service managing enterprise computer/workstation hardware binding for hospital accounts.
 *
 * Rules:
 * - Each account is locked to 1–3 authorized workstations.
 * - Unrecognized/unbound workstations require explicit Super Admin approval.
 * - Bound workstations can be managed or revoked by Super Admin at any time.
 */
final class WorkstationBindingService
{
    public const COOKIE_NAME = 'fms_workstation_token';

    /**
     * Resolve or generate a persistent workstation device UUID.
     * Checks request headers, input parameters, and cookies.
     */
    public function resolveDeviceUuid(Request $request): string
    {
        $uuid = (string) (
            $request->header('X-Workstation-UUID')
            ?? $request->input('device_uuid')
            ?? $request->cookie(self::COOKIE_NAME)
            ?? ($request->hasSession() ? ($request->session()->get('auth.pending_device_uuid') ?? $request->session()->get('auth.device_uuid')) : null)
        );

        if (! empty($uuid) && preg_match('/^[a-zA-Z0-9_-]{16,64}$/', $uuid)) {
            return $uuid;
        }

        return (string) Str::uuid();
    }

    /**
     * Create an HTTP-only persistent cookie containing the device UUID.
     * Valid for 5 years (persistent hardware binding).
     */
    public function createDeviceCookie(string $deviceUuid): SymfonyCookie
    {
        return Cookie::make(
            name:     self::COOKIE_NAME,
            value:    $deviceUuid,
            minutes:  60 * 24 * 365 * 5, // 5 years
            path:     '/',
            domain:   null,
            secure:   request()->isSecure(),
            httpOnly: true,
            sameSite: 'lax',
        );
    }

    /**
     * Detect client platform/OS from user agent.
     */
    public function detectPlatform(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Unknown OS';
        }

        return match (true) {
            str_contains($userAgent, 'Windows NT 10.0') || str_contains($userAgent, 'Windows NT 11.0') => 'Windows 10/11',
            str_contains($userAgent, 'Windows NT 6.3')  => 'Windows 8.1',
            str_contains($userAgent, 'Windows NT 6.1')  => 'Windows 7',
            str_contains($userAgent, 'Macintosh')       => 'macOS',
            str_contains($userAgent, 'iPhone')          => 'iOS Mobile',
            str_contains($userAgent, 'Android')         => 'Android Mobile',
            str_contains($userAgent, 'Linux')           => 'Linux Workstation',
            default                                     => 'Workstation Terminal',
        };
    }

    /**
     * Detect client browser from user agent.
     */
    public function detectBrowser(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Unknown Browser';
        }

        return match (true) {
            str_contains($userAgent, 'Edg/')     => 'Microsoft Edge',
            str_contains($userAgent, 'Chrome/')  => 'Google Chrome',
            str_contains($userAgent, 'Firefox/') => 'Mozilla Firefox',
            str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome') => 'Apple Safari',
            default                              => 'Hospital Web Client',
        };
    }

    /**
     * Generate a default friendly workstation label based on platform, browser, and IP.
     */
    public function generateWorkstationLabel(Request $request): string
    {
        $platform = $this->detectPlatform($request->userAgent());
        $browser  = $this->detectBrowser($request->userAgent());
        $ip       = $request->ip();

        return "{$platform} ({$browser}) - {$ip}";
    }

    /**
     * Evaluate the workstation authorization status for a user attempt.
     *
     * @return array{
     *     status: string,
     *     workstation: ?UserWorkstation,
     *     message: ?string,
     *     can_request: bool
     * }
     */
    public function evaluateWorkstation(User $user, string $deviceUuid, Request $request): array
    {
        /** @var UserWorkstation|null $workstation */
        $workstation = UserWorkstation::where('user_id', $user->id)
            ->where('device_uuid', $deviceUuid)
            ->first();

        if ($workstation) {
            // Update last seen metadata
            $workstation->update([
                'last_seen_at' => now(),
                'ip_address'   => $request->ip(),
                'platform'     => $this->detectPlatform($request->userAgent()),
                'browser'      => $this->detectBrowser($request->userAgent()),
            ]);

            return match ($workstation->status) {
                UserWorkstation::STATUS_APPROVED => [
                    'status'      => UserWorkstation::STATUS_APPROVED,
                    'workstation' => $workstation,
                    'message'     => null,
                    'can_request' => false,
                ],
                UserWorkstation::STATUS_PENDING => [
                    'status'      => UserWorkstation::STATUS_PENDING,
                    'workstation' => $workstation,
                    'message'     => 'This workstation is currently awaiting authorization from the Super Administrator.',
                    'can_request' => false,
                ],
                UserWorkstation::STATUS_REJECTED => [
                    'status'      => UserWorkstation::STATUS_REJECTED,
                    'workstation' => $workstation,
                    'message'     => 'Authorization for this workstation was rejected by the Super Administrator.',
                    'can_request' => false,
                ],
                UserWorkstation::STATUS_REVOKED => [
                    'status'      => UserWorkstation::STATUS_REVOKED,
                    'workstation' => $workstation,
                    'message'     => 'Access from this workstation was revoked. Contact IT / Super Administrator to re-authorize.',
                    'can_request' => true,
                ],
                default => [
                    'status'      => 'unrecognized',
                    'workstation' => null,
                    'message'     => 'Unrecognized workstation.',
                    'can_request' => true,
                ],
            };
        }

        return [
            'status'      => 'unrecognized',
            'workstation' => null,
            'message'     => 'This workstation is not recognized or bound to your account.',
            'can_request' => true,
        ];
    }

    /**
     * Submit an explicit workstation authorization request to the Super Admin.
     */
    public function submitAuthorizationRequest(
        User $user,
        string $deviceUuid,
        Request $request,
        ?string $customLabel = null
    ): UserWorkstation {
        $approvedCount = $user->approvedWorkstations()->count();
        $label = $customLabel ?: $this->generateWorkstationLabel($request);

        /** @var UserWorkstation $workstation */
        $workstation = UserWorkstation::updateOrCreate(
            [
                'user_id'     => $user->id,
                'device_uuid' => $deviceUuid,
            ],
            [
                'workstation_name' => $label,
                'platform'         => $this->detectPlatform($request->userAgent()),
                'browser'          => $this->detectBrowser($request->userAgent()),
                'ip_address'       => $request->ip(),
                'status'           => UserWorkstation::STATUS_PENDING,
                'rejection_reason' => null,
                'rejected_at'      => null,
                'revoked_at'       => null,
                'last_seen_at'     => now(),
            ]
        );

        ActivityLog::logAuth(
            event:       'workstation_auth_requested',
            user:        $user,
            description: "Workstation authorization requested for user [{$user->name}] ({$user->role}) on [{$label}]. Approved count: {$approvedCount}/" . UserWorkstation::MAX_PER_USER,
            ip:          $request->ip(),
            userAgent:   $request->userAgent()
        );

        return $workstation;
    }

    /**
     * Super Admin action: Approve a pending or re-authorizing workstation.
     * Enforces the hard limit of 1–3 workstations per user account.
     *
     * @throws ValidationException
     */
    public function approveWorkstation(
        UserWorkstation $workstation,
        User $admin,
        ?string $customName = null
    ): void {
        $user = $workstation->user;

        // Enforce maximum 3 authorized workstations per user
        $currentApprovedCount = $user->approvedWorkstations()
            ->where('id', '!=', $workstation->id)
            ->count();

        if ($currentApprovedCount >= UserWorkstation::MAX_PER_USER) {
            throw ValidationException::withMessages([
                'workstation' => "User [{$user->name}] has already reached the maximum limit of " . UserWorkstation::MAX_PER_USER . " bound workstations. Revoke an existing workstation before approving a new one.",
            ]);
        }

        $workstation->update([
            'status'           => UserWorkstation::STATUS_APPROVED,
            'workstation_name' => $customName ?: $workstation->workstation_name,
            'approved_by'      => $admin->id,
            'approved_at'      => now(),
            'rejected_at'      => null,
            'rejection_reason' => null,
            'revoked_at'       => null,
            'revoked_by'       => null,
        ]);

        ActivityLog::logAuth(
            event:       'workstation_approved',
            user:        $user,
            description: "Super Admin [{$admin->name}] approved workstation [{$workstation->workstation_name}] for user [{$user->name}].",
            ip:          request()->ip(),
            userAgent:   request()->userAgent()
        );
    }

    /**
     * Super Admin action: Reject a pending workstation request.
     */
    public function rejectWorkstation(
        UserWorkstation $workstation,
        User $admin,
        ?string $reason = null
    ): void {
        $user = $workstation->user;

        $workstation->update([
            'status'           => UserWorkstation::STATUS_REJECTED,
            'rejected_at'      => now(),
            'rejection_reason' => $reason ?: 'Rejected by Super Administrator.',
        ]);

        ActivityLog::logAuth(
            event:       'workstation_rejected',
            user:        $user,
            description: "Super Admin [{$admin->name}] rejected workstation [{$workstation->workstation_name}] for user [{$user->name}]. Reason: {$workstation->rejection_reason}",
            ip:          request()->ip(),
            userAgent:   request()->userAgent()
        );
    }

    /**
     * Super Admin action: Revoke an authorized workstation.
     * Immediately revokes binding and terminates any active sessions on that machine.
     */
    public function revokeWorkstation(UserWorkstation $workstation, User $admin): void
    {
        $user = $workstation->user;

        $workstation->update([
            'status'     => UserWorkstation::STATUS_REVOKED,
            'revoked_at' => now(),
            'revoked_by' => $admin->id,
        ]);

        // Terminate any active sessions connected to this workstation
        $workstation->activeSessions()
            ->where('is_terminated', false)
            ->each(function ($session) {
                $session->terminate(UserActiveSession::REASON_WORKSTATION_REVOKED);
            });

        ActivityLog::logAuth(
            event:       'workstation_revoked',
            user:        $user,
            description: "Super Admin [{$admin->name}] revoked workstation [{$workstation->workstation_name}] for user [{$user->name}]. All active sessions terminated.",
            ip:          request()->ip(),
            userAgent:   request()->userAgent()
        );
    }

    /**
     * Retrieve all pending workstation authorization requests across the hospital system.
     *
     * @return Collection<int, UserWorkstation>
     */
    public function getPendingRequests(): Collection
    {
        return UserWorkstation::with(['user'])
            ->pending()
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * Retrieve all bound workstations, optionally filtered by user ID.
     *
     * @return Collection<int, UserWorkstation>
     */
    public function getBoundWorkstations(?int $userId = null): Collection
    {
        $query = UserWorkstation::with(['user', 'approver', 'revoker'])
            ->whereIn('status', [UserWorkstation::STATUS_APPROVED, UserWorkstation::STATUS_REVOKED, UserWorkstation::STATUS_REJECTED])
            ->orderByDesc('last_seen_at');

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }
}
