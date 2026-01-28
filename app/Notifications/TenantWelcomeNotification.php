<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Tenant $tenant
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
        $trialDays = $this->tenant->trialDaysRemaining();

        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . '!')
            ->greeting('Welcome, ' . $notifiable->name . '!')
            ->line('Your account has been created successfully.')
            ->line('**Your workspace details:**')
            ->line('- **Company:** ' . $this->tenant->name)
            ->line('- **URL:** ' . $tenantUrl)
            ->line('- **Trial Period:** ' . $trialDays . ' days remaining')
            ->action('Go to Your Dashboard', $tenantUrl)
            ->line('Here are some things you can do to get started:')
            ->line('1. Set up your company profile')
            ->line('2. Add your first items/services')
            ->line('3. Create categories for organization')
            ->line('4. Invite team members')
            ->line('If you have any questions, our support team is here to help.')
            ->salutation('Welcome aboard!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id' => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'type' => 'tenant_welcome',
        ];
    }
}
