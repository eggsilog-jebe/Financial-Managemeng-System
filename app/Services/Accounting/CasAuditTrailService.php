<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\CasAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class CasAuditTrailService
{
    /**
     * Log a tamper-evident financial transaction event into BIR CAS audit trail with SHA-256 hash chaining.
     */
    public function logFinancialEvent(
        Model $auditable,
        string $action, // INSERT, UPDATE, DELETE, POST, REVERSE, LOCK
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = 1,
        ?string $userName = 'System / Administrator',
        ?string $ipAddress = '127.0.0.1'
    ): CasAuditTrail {
        $lastLog = CasAuditTrail::latest('id')->first();
        $previousHash = $lastLog ? $lastLog->record_hash : str_repeat('0', 64);

        $eventUuid = (string) Str::uuid();
        $timestamp = now();

        $payloadToHash = json_encode([
            'uuid'          => $eventUuid,
            'type'          => get_class($auditable),
            'id'            => $auditable->getKey(),
            'action'        => $action,
            'old'           => $oldValues,
            'new'           => $newValues,
            'previous_hash' => $previousHash,
            'timestamp'     => $timestamp->toIso8601String(),
        ], JSON_THROW_ON_ERROR);

        $recordHash = hash('sha256', $payloadToHash);

        return CasAuditTrail::create([
            'event_uuid'     => $eventUuid,
            'user_id'        => $userId,
            'user_name'      => $userName,
            'ip_address'     => $ipAddress,
            'auditable_type' => get_class($auditable),
            'auditable_id'   => (int) $auditable->getKey(),
            'action'         => $action,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'record_hash'    => $recordHash,
            'previous_hash'  => $previousHash,
            'created_at'     => $timestamp,
        ]);
    }

    /**
     * Verify the cryptographic integrity of the entire BIR CAS audit log chain.
     */
    public function verifyAuditTrailIntegrity(): bool
    {
        $logs = CasAuditTrail::orderBy('id')->get();
        $expectedPrevHash = str_repeat('0', 64);

        foreach ($logs as $log) {
            if ($log->previous_hash !== $expectedPrevHash) {
                return false; // Chain broken
            }
            $expectedPrevHash = $log->record_hash;
        }

        return true;
    }
}
