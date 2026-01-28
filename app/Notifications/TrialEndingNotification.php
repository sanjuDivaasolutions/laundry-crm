<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialEndingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Tenant $tenant,
        protected int $daysRemaining
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $tenantUrl = 'https://' . $this->tenant->domain . '.' . config('tenancy.base_domain');
        $upgradeUrl = $tenantUrl . '/admin#/billing/upgrade';

        $message = (new MailMessage)
            ->subject('Your Trial Ends in ' . $this->daysRemaining . ' Days - ' . config('app.name'));

        if ($this->daysRemaining <= 1) {
            $message->greeting('Final Reminder!')
                ->line('Your trial period for **' . $this->tenant->name . '** ends tomorrow.')
                ->line('After your trial ends, your account will become read-only. You will still be able to view your data, but you won\'t be able to create or modify records.')
                ->line('**Don\'t lose access to these features:**')
                ->line('- Create orders and manage customers')
                ->line('- Track inventory and items')
                ->line('- Generate reports')
                ->line('- Full team collaboration');
        } elseif ($this->daysRemaining <= 3) {
            $message->greeting('Your Trial is Almost Over')
                ->line('You have **' . $this->daysRemaining . ' days** left in your trial for **' . $this->tenant->name . '**.')
                ->line('Upgrade now to continue enjoying all features without interruption.');
        } else {
            $message->greeting('Trial Reminder')
                ->line('You have **' . $this->daysRemaining . ' days** remaining in your trial for **' . $this->tenant->name . '**.')
                ->line('We hope you\'re enjoying the platform! When you\'re ready, upgrade to a paid plan to keep all your data and continue using all features.');
        }

        return $message
            ->action('Upgrade Now', $upgradeUrl)
            ->line('Need more time? Contact our support team to discuss your options.')
            ->salutation('The ' . config('app.name') . ' Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id' => $this->tenant->id,
            'days_remaining' => $this->daysRemaining,
            'type' => 'trial_ending',
        ];
    }
}
