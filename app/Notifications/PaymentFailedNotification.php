<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when a payment fails (dunning).
 */
class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $invoiceId;
    public int $amountDue;
    public int $attemptCount;
    public ?string $failureReason;
    public int $graceDays;

    public function __construct(
        int|string $attemptCountOrInvoiceId,
        int $amountDueOrGraceDays = 0,
        int $attemptCount = 1,
        ?string $failureReason = null
    ) {
        // Support both old signature (invoiceId, amountDue, attemptCount, failureReason)
        // and new signature (attemptCount, graceDays)
        if (is_string($attemptCountOrInvoiceId)) {
            // Old signature
            $this->invoiceId = $attemptCountOrInvoiceId;
            $this->amountDue = $amountDueOrGraceDays;
            $this->attemptCount = $attemptCount;
            $this->failureReason = $failureReason;
            $this->graceDays = 7;
        } else {
            // New signature (attemptCount, graceDays)
            $this->invoiceId = '';
            $this->amountDue = 0;
            $this->attemptCount = $attemptCountOrInvoiceId;
            $this->graceDays = $amountDueOrGraceDays;
            $this->failureReason = null;
        }
    }

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
        $tenant = $notifiable->tenant ?? null;
        $billingUrl = $tenant ? $tenant->getUrl() . '/admin#/billing' : url('/billing');

        $mail = (new MailMessage)
            ->subject('Payment Failed - Action Required')
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',');

        if ($this->amountDue > 0) {
            $amount = number_format($this->amountDue / 100, 2);
            $mail->line("We were unable to process your payment of \${$amount}.");
        } else {
            $mail->line("We were unable to process your subscription payment.");
        }

        $mail->line("This is attempt #{$this->attemptCount}.");

        if ($this->failureReason) {
            $mail->line("Reason: {$this->failureReason}");
        }

        if ($this->graceDays > 0) {
            $mail->line("You have **{$this->graceDays} days** to update your payment method before your account access is restricted.");
        }

        $mail->line('Please update your payment method to avoid service interruption.')
            ->action('Update Payment Method', $billingUrl)
            ->line('If you believe this is an error, please contact support.');

        if ($this->attemptCount >= 3) {
            $mail->line('**Warning:** Your subscription may be canceled if payment is not received soon.');
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_failed',
            'invoice_id' => $this->invoiceId,
            'amount_due' => $this->amountDue,
            'attempt_count' => $this->attemptCount,
            'failure_reason' => $this->failureReason,
        ];
    }
}
