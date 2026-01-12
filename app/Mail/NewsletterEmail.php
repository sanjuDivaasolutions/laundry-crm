<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public Message $message;

    public function __construct($message)
    {
        $this->company = $message->company;
        $this->message = $message;
    }

    public function build()
    {
        return $this->subject($this->message->subject)
            ->view('newsletter.email')
            ->with([
                'company' => $this->company,
                'content' => $this->message->message,
            ]);
    }
}

