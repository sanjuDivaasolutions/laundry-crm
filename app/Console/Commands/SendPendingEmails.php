<?php

// app/Console/Commands/SendPendingEmails.php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmails;
use App\Models\Message;
use Illuminate\Console\Command;

class SendPendingEmails extends Command
{
    protected $signature = 'emails:send-pending';

    protected $description = 'Send pending emails with status=0';

    public function handle()
    {
        $messages = Message::query()
            ->with(['company'])
            ->where('status', 0)
            ->where('schedule_at', '<=', now())
            ->get();

        if ($messages->isEmpty()) {
            return;
        }

        foreach ($messages as $message) {
            SendQueuedEmails::dispatch($message);
        }

        $this->info('Pending emails have been dispatched.');
    }
}
