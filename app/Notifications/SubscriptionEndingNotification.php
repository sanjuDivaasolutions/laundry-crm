<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a subscription is about to end.
 */
class SubscriptionEndingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $endsAt,
        public bool $isCanceled = false
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',');

        if ($this->isCanceled) {
            $mail->subject('Your Subscription Has Been Canceled')
                ->line('Your subscription has been canceled and will end on ' . $this->endsAt . '.')
                ->line('You can continue using all features until then.')
                ->line('Changed your mind? You can resume your subscription anytime before it ends.')
                ->action('Resume Subscription', url('/subscription'));
        } else {
            $mail->subject('Subscription Ending Soon')
                ->line('Your subscription will end on ' . $this->endsAt . '.')
                ->line('To continue using our services, please renew your subscription.')
                ->action('View Subscription', url('/subscription'));
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_ending',
            'ends_at' => $this->endsAt,
            'is_canceled' => $this->isCanceled,
        ];
    }
}
