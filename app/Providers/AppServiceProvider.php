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
        \Illuminate\Support\Facades\Gate::define('access-cashier-pos', function ($user): bool {
            return in_array($user->role, ['Cashier', 'CFO', 'FinanceDirector'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-ar-billing', function ($user): bool {
            return in_array($user->role, ['BillingClerk', 'StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-ap-procurement', function ($user): bool {
            return in_array($user->role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector'], true);
        });

        \Illuminate\Support\Facades\Gate::define('access-disbursements', function ($user): bool {
            return in_array($user->role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector'], true);
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

        \Illuminate\Support\Facades\Gate::define('post-journal-entries', function ($user): bool {
            return in_array($user->role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector'], true);
        });

        \Illuminate\Support\Facades\Gate::define('reverse-journal-entries', function ($user): bool {
            return in_array($user->role, ['FinanceManager', 'CFO', 'FinanceDirector'], true);
        });
    }
}
