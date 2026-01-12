<?php
// app/Jobs/SendQueuedEmails.php

namespace App\Jobs;

use App\Mail\NewsletterEmail;
use App\Models\Message;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendQueuedEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function handle()
    {
        $subscribers = Subscriber::query()->where('company_id', $this->message->company_id)->get();

        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)->send(new NewsletterEmail($this->message));
        }

        $this->message->update(['status' => 1]);
    }
}
