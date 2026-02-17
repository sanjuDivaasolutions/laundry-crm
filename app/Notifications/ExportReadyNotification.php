<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $module,
        public string $fileName,
        public string $format,
        public string $downloadPath
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your {$this->module} export is ready")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your {$this->format} export of {$this->module} is ready for download.")
            ->action('Download', url("/api/v1/exports/download/{$this->fileName}"))
            ->line('The file will be available for 24 hours.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'module' => $this->module,
            'file_name' => $this->fileName,
            'format' => $this->format,
            'download_path' => $this->downloadPath,
            'message' => ucfirst($this->module)." {$this->format} export is ready for download.",
        ];
    }
}
