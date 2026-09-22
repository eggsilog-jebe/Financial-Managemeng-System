<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS for all URLs in production (Vercel terminates SSL at edge,
        // forwards requests as HTTP internally — without this, form actions and
        // route() helper generate http:// URLs which browsers block as insecure).
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Define Gates for Financial Segregation of Duties (SoD)
        \Illuminate\Support\Facades\Gate::before(function ($user, string $ability): ?bool {
            // In local development or demo exploration without login, allow full visibility
            if (! $user && app()->environment('local', 'testing')) {
                return true;
            }

            // CFO and FinanceDirector always have full superuser access to all modules
            if ($user && in_array($user->role, ['CFO', 'FinanceDirector'], true)) {
                return true;
            }

            return null;
        });

        \Illuminate\Support\Facades\Gate::define('access-cashier-pos', function ($user): bool {
            return in_array($user->role, ['Cashier', 'StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector', 'Auditor'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-ar-billing', function ($user): bool {
            return in_array($user->role, ['BillingClerk', 'StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector', 'Auditor'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-ap-procurement', function ($user): bool {
            return in_array($user->role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector', 'Auditor'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-disbursements', function ($user): bool {
            return in_array($user->role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector', 'Auditor'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-budget', function ($user): bool {
            return in_array($user->role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector', 'Auditor'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-cash-management', function ($user): bool {
            return in_array($user->role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector', 'Auditor'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-tax-management', function ($user): bool {
            return in_array($user->role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector', 'Auditor'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-general-ledger', function ($user): bool {
            return in_array($user->role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector', 'Auditor'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-financial-reports', function ($user): bool {
            return in_array($user->role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector', 'Auditor'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-period-closing', function ($user): bool {
            return in_array($user->role, ['CFO', 'FinanceDirector'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-user-management', function ($user): bool {
            return in_array($user->role, ['CFO', 'FinanceDirector'], true);
        });

        \Illuminate\Support\Facades\Gate::define('post-journal-entries', function ($user): bool {
            return in_array($user->role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector'], true);
        });

        \Illuminate\Support\Facades\Gate::define('reverse-journal-entries', function ($user): bool {
            return in_array($user->role, ['FinanceManager', 'CFO', 'FinanceDirector'], true);
        });

        // Register Audit Trail Observers for financial entities
        $observedModels = [
            \App\Models\JournalEntry::class,
            \App\Models\JournalEntryLine::class,
            \App\Models\PatientAccount::class,
            \App\Models\Invoice::class,
            \App\Models\PurchaseBill::class,
            \App\Models\Vendor::class,
            \App\Models\DisbursementVoucher::class,
            \App\Models\CheckRegister::class,
            \App\Models\OfficialReceipt::class,
            \App\Models\CashierShift::class,
            \App\Models\BudgetAllocation::class,
            \App\Models\BankAccount::class,
            \App\Models\BankReconciliation::class,
            \App\Models\GuaranteeLetter::class,
            \App\Models\User::class,
        ];

        foreach ($observedModels as $modelClass) {
            $modelClass::observe(\App\Observers\ActivityLogObserver::class);
        }
    }
}
