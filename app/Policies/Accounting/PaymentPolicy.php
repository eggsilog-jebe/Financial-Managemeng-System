<?php

declare(strict_types=1);

namespace App\Policies\Accounting;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

final class PaymentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view payment records and cashier collections.
     */
    public function viewAny(User $user): Response
    {
        return in_array($user->role ?? 'Cashier', ['Cashier', 'BillingClerk', 'StaffAccountant', 'FinanceManager', 'CFO', 'Auditor'], true)
            ? Response::allow()
            : Response::deny('Unauthorized: Access restricted to authorized billing and finance staff.');
    }

    /**
     * Determine whether the user can accept collections and issue official receipts (OR).
     */
    public function createCollection(User $user): Response
    {
        return in_array($user->role ?? 'Cashier', ['Cashier', 'FinanceManager', 'CFO'], true)
            ? Response::allow()
            : Response::deny('Unauthorized: Only designated Cashiers can accept patient payments and issue Official Receipts.');
    }

    /**
     * Determine whether the user can approve disbursement vouchers.
     */
    public function approveDisbursement(User $user): Response
    {
        return in_array($user->role ?? 'FinanceManager', ['FinanceManager', 'CFO'], true)
            ? Response::allow()
            : Response::deny('Unauthorized: Disbursement voucher approvals require Finance Manager or CFO authorization.');
    }

    /**
     * Enforce immutability: Collections and receipts cannot be hard deleted.
     */
    public function delete(User $user, Payment $payment): Response
    {
        return Response::deny('BIR CAS Compliance: Official collection receipts and payments cannot be deleted.');
    }
}
