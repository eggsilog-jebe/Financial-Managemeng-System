<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserActiveSession;
use App\Models\UserWorkstation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * Service managing enterprise single active session enforcement and live session tracking.
 *
 * Requirements:
 * - Single active session enforcement: if another user or workstation logs into the same account,
 *   terminate previous session immediately with a displacement security warning.
 * - Super Admin can view active sessions and force-terminate them in real-time.
 */
final class ActiveSessionManagerService
{
    /**
     * Register a new active session upon successful login/verification.
     * Enforces Single Active Session: instantly terminates any existing sessions for this user.
     */
    public function registerSession(
        User $user,
        string $sessionId,
        ?UserWorkstation $workstation,
        Request $request
    ): UserActiveSession {
        // 1. Terminate all other active sessions for this user (Displacement Rule)
        $previousActiveSessions = UserActiveSession::where('user_id', $user->id)
            ->where('is_terminated', false)
            ->where('session_id', '!=', $sessionId)
            ->get();

        foreach ($previousActiveSessions as $oldSession) {
            $oldSession->terminate(UserActiveSession::REASON_DISPLACED);

            ActivityLog::logAuth(
                event:       'session_displaced',
                user:        $user,
                description: "Session [{$oldSession->session_id}] for user [{$user->name}] was terminated due to a new login from IP [{$request->ip()}].",
                ip:          $request->ip(),
                userAgent:   $request->userAgent()
            );
        }

        // 2. Create or reactivate the current session
        /** @var UserActiveSession $activeSession */
        $activeSession = UserActiveSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id'            => $user->id,
                'workstation_id'     => $workstation?->id,
                'device_uuid'        => $workstation?->device_uuid,
                'device_name'        => $workstation?->workstation_name ?: $request->userAgent(),
                'ip_address'         => $request->ip(),
                'user_agent'         => $request->userAgent(),
                'login_at'           => now(),
                'last_activity_at'   => now(),
                'is_terminated'      => false,
                'termination_reason' => null,
                'terminated_at'      => null,
            ]
        );

        return $activeSession;
    }

    /**
     * Update the last activity timestamp for an active session.
     */
    public function touchSession(string $sessionId): void
    {
        UserActiveSession::where('session_id', $sessionId)
            ->where('is_terminated', false)
            ->update(['last_activity_at' => now()]);
    }

    /**
     * Check if a session has been terminated or displaced.
     *
     * @return string|null Reason string if terminated/displaced, null if still active and valid.
     */
    public function checkSessionDisplacement(string $sessionId, User $user): ?string
    {
        /** @var UserActiveSession|null $record */
        $record = UserActiveSession::where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        // If explicitly marked as terminated, return the reason
        if ($record && $record->is_terminated) {
            return $record->termination_reason ?: UserActiveSession::REASON_DISPLACED;
        }

        // Check if there is another newer active session for this user (displacement guard)
        $newerSessionExists = UserActiveSession::where('user_id', $user->id)
            ->where('is_terminated', false)
            ->where('session_id', '!=', $sessionId)
            ->where('login_at', '>', $record?->login_at ?? now()->subDay())
            ->exists();

        if ($newerSessionExists) {
            if ($record) {
                $record->terminate(UserActiveSession::REASON_DISPLACED);
            }
            return UserActiveSession::REASON_DISPLACED;
        }

        return null;
    }

    /**
     * Force-terminate an active session by Super Admin.
     */
    public function terminateSession(
        UserActiveSession $session,
        User $admin,
        string $reason = UserActiveSession::REASON_ADMIN_REVOKED
    ): void {
        $session->terminate($reason);

        ActivityLog::logAuth(
            event:       'session_force_terminated',
            user:        $session->user,
            description: "Super Admin [{$admin->name}] force-terminated session [{$session->session_id}] for user [{$session->user?->name}]. Reason: {$reason}",
            ip:          request()->ip(),
            userAgent:   request()->userAgent()
        );
    }

    /**
     * Terminate the session on explicit user logout.
     */
    public function terminateCurrentSession(string $sessionId): void
    {
        UserActiveSession::where('session_id', $sessionId)
            ->where('is_terminated', false)
            ->each(function ($session) {
                $session->terminate(UserActiveSession::REASON_MANUAL_LOGOUT);
            });
    }

    /**
     * Retrieve all active live sessions across the system.
     *
     * @return Collection<int, UserActiveSession>
     */
    public function getActiveSessions(): Collection
    {
        return UserActiveSession::with(['user', 'workstation'])
            ->active()
            ->orderByDesc('last_activity_at')
            ->get();
    }
}
