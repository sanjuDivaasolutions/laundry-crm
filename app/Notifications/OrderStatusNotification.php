<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $statusType,
        public string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting("Hello {$notifiable->name}!");

        return match ($this->statusType) {
            'ready' => $message
                ->line("Your laundry order **{$this->order->order_number}** is ready for pickup!")
                ->line('Please pick up your items at your earliest convenience.')
                ->line("Order Date: {$this->order->order_date?->format('M d, Y')}")
                ->line("Total: {$this->order->total_amount}")
                ->action('View Order', url("/orders/{$this->order->id}"))
                ->line('Thank you for choosing our laundry service!'),
            'processing' => $message
                ->line("Your laundry order **{$this->order->order_number}** is now being processed.")
                ->line("Current status: **{$this->newStatus}**")
                ->line("Expected ready date: {$this->order->promised_date?->format('M d, Y')}")
                ->action('Track Order', url("/orders/{$this->order->id}"))
                ->line('We will notify you when your order is ready.'),
            'completed' => $message
                ->line("Your laundry order **{$this->order->order_number}** has been completed and picked up.")
                ->line("Total paid: {$this->order->paid_amount}")
                ->action('View Receipt', url("/orders/{$this->order->id}"))
                ->line('Thank you for your business!'),
            default => $message
                ->line("Your laundry order **{$this->order->order_number}** status has been updated.")
                ->line("New status: **{$this->newStatus}**")
                ->action('View Order', url("/orders/{$this->order->id}"))
                ->line('Thank you for choosing our laundry service!'),
        };
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status_type' => $this->statusType,
            'new_status' => $this->newStatus,
            'message' => $this->getSubject(),
        ];
    }

    private function getSubject(): string
    {
        return match ($this->statusType) {
            'ready' => "Order {$this->order->order_number} - Ready for Pickup",
            'processing' => "Order {$this->order->order_number} - Now {$this->newStatus}",
            'completed' => "Order {$this->order->order_number} - Completed",
            default => "Order {$this->order->order_number} - Status Update",
        };
    }
}
