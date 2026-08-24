<?php

declare(strict_types=1);

namespace App\Policies\Accounting;

use App\Models\FiscalPeriod;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

final class FiscalPeriodPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view fiscal period closing statuses.
     */
    public function viewAny(User $user): Response
    {
        return in_array($user->role ?? 'StaffAccountant', ['StaffAccountant', 'FinanceManager', 'CFO', 'Auditor'], true)
            ? Response::allow()
            : Response::deny('Unauthorized: Access restricted to Finance leadership and Audit.');
    }

    /**
     * Only CFO or Finance Director can execute period-end closing locks.
     */
    public function closePeriod(User $user, FiscalPeriod $fiscalPeriod): Response
    {
        return in_array($user->role ?? 'CFO', ['CFO', 'FinanceManager'], true)
            ? Response::allow()
            : Response::deny('Unauthorized: Fiscal period locking strictly requires Chief Financial Officer (CFO) or Finance Director authorization.');
    }
}
