<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Notifications\TrialEndingNotification;
use Illuminate\Console\Command;

class SendTrialEndingNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenants:send-trial-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send trial ending reminder notifications to tenants';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $warningDays = config('tenancy.trial.warning_days', 3);

        $this->info("Checking for trials ending within {$warningDays} days...");

        // Find tenants with trials ending within warning period
        $tenants = Tenant::query()
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->where('trial_ends_at', '<=', now()->addDays($warningDays))
            ->whereDoesntHave('subscriptions', function ($query) {
                $query->where('stripe_status', 'active');
            })
            ->get();

        $count = 0;

        foreach ($tenants as $tenant) {
            $daysRemaining = $tenant->trialDaysRemaining();

            // Only send notifications at specific intervals: 3 days, 1 day, and day of
            if (!in_array($daysRemaining, [0, 1, 3])) {
                continue;
            }

            // Get admin users for this tenant
            $admins = $tenant->users()
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'admin');
                })
                ->get();

            foreach ($admins as $admin) {
                // Check if we already sent this notification today
                $alreadySent = $admin->notifications()
                    ->where('type', TrialEndingNotification::class)
                    ->whereDate('created_at', today())
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $admin->notify(new TrialEndingNotification($tenant, $daysRemaining));
                $count++;

                $this->line("  Sent to {$admin->email} ({$tenant->name}) - {$daysRemaining} days remaining");
            }
        }

        $this->info("Sent {$count} trial ending notifications.");

        return Command::SUCCESS;
    }
}
