<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Auth\TwoFactorAuthService;
use Illuminate\Console\Command;

final class TwoFactorCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = '2fa:code 
                            {email? : User email address (defaults to g59kamikaze@gmail.com)}
                            {--reset : Reset 2FA for this user so they can test the QR setup flow from scratch}';

    /**
     * The console command description.
     */
    protected $description = 'Generate current TOTP 2FA code or reset 2FA enrollment for testing';

    public function handle(TwoFactorAuthService $twoFactorService): int
    {
        $email = $this->argument('email') ?? 'g59kamikaze@gmail.com';

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email [{$email}] not found.");
            return self::FAILURE;
        }

        if ($this->option('reset')) {
            $user->update([
                'two_factor_secret'         => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at'   => null,
            ]);
            $this->info("✅ 2FA has been reset for [{$email}].");
            $this->comment("Next time this user logs in, they will be prompted to set up Google Authenticator via QR code.");
            return self::SUCCESS;
        }

        $secret = $twoFactorService->getDecryptedSecret($user);

        if (! $secret) {
            $this->warn("User [{$email}] does not have 2FA configured yet.");
            $this->comment("Log in as this user to trigger the 2FA QR code setup screen.");
            return self::SUCCESS;
        }

        $otp = $twoFactorService->getCurrentOtp($user);
        $secondsRemaining = 30 - (time() % 30);

        $this->newLine();
        $this->info("=================================================");
        $this->info("  HIMS • FMS Two-Factor Authentication (TOTP)   ");
        $this->info("=================================================");
        $this->line(" <fg=gray>User:</>       <fg=bright-white>{$user->name}</> ({$user->email})");
        $this->line(" <fg=gray>Role:</>       <fg=bright-white>{$user->role}</>");
        $this->line(" <fg=gray>Status:</>     " . ($user->hasTwoFactorEnabled() ? '<fg=green>Active & Confirmed</>' : '<fg=yellow>Pending Confirmation</>'));
        $this->newLine();
        $this->line(" <fg=gray>Current Code:</> <fg=bright-green;options=bold,underscore> {$otp} </>  <fg=yellow>({$secondsRemaining}s remaining)</>");
        $this->newLine();
        $this->line(" <fg=gray>Secret Key:</>   <fg=cyan>{$secret}</>");
        $this->line(" <fg=gray>(You can manually type this secret key into Google Authenticator on your phone)</>");
        $this->info("=================================================");
        $this->newLine();

        return self::SUCCESS;
    }
}
