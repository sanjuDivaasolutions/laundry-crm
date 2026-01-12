<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterLog extends Model
{
    use HasFactory;

    protected $fillable = ['subscriber_id', 'message_id', 'sent_at'];

    public $timestamps = false;

    /**
     * Get the subscriber who received the email.
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    /**
     * Get the message that was sent.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}

