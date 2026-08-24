<?php

declare(strict_types=1);

namespace App\Policies\Accounting;

use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

final class JournalEntryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any journal entries.
     */
    public function viewAny(User $user): Response
    {
        return in_array($user->role ?? 'StaffAccountant', ['StaffAccountant', 'FinanceManager', 'CFO', 'Auditor'], true)
            ? Response::allow()
            : Response::deny('Unauthorized: Access restricted to Accounting and Audit personnel.');
    }

    /**
     * Determine whether the user can view a specific journal entry.
     */
    public function view(User $user, JournalEntry $journalEntry): Response
    {
        return in_array($user->role ?? 'StaffAccountant', ['StaffAccountant', 'FinanceManager', 'CFO', 'Auditor'], true)
            ? Response::allow()
            : Response::deny('Unauthorized: Access restricted to Accounting and Audit personnel.');
    }

    /**
     * Determine whether the user can create journal entries.
     */
    public function create(User $user): Response
    {
        return in_array($user->role ?? 'StaffAccountant', ['StaffAccountant', 'FinanceManager', 'CFO'], true)
            ? Response::allow()
            : Response::deny('Unauthorized: Only Staff Accountants and Finance Managers can create journal entries.');
    }

    /**
     * Determine whether the user can reverse a posted journal entry.
     */
    public function reverse(User $user, JournalEntry $journalEntry): Response
    {
        if ($journalEntry->status !== 'POSTED') {
            return Response::deny('Only POSTED journal entries can be reversed.');
        }

        return in_array($user->role ?? 'FinanceManager', ['FinanceManager', 'CFO'], true)
            ? Response::allow()
            : Response::deny('Unauthorized: Journal entry reversals require Finance Manager or CFO approval.');
    }

    /**
     * Invariance Rule: Zero roles can force-delete posted general ledger transactions.
     */
    public function delete(User $user, JournalEntry $journalEntry): Response
    {
        return Response::deny('GAAP / BIR Compliance Violation: Posted financial records cannot be deleted. Corrections must strictly generate reversal journal entries.');
    }
}
