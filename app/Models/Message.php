<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Message extends Model
{
    use HasFactory, HasAdvancedFilter;

    protected array $orderable = ['id', 'subject', 'message', 'schedule_at', 'status'];

    protected array $filterable = ['id', 'subject', 'message', 'schedule_at', 'status', 'company_id'];

    protected $fillable = ['subject', 'message', 'schedule_at', 'status', 'company_id'];

    protected $appends = ['status_label'];

    public function getScheduleAtAttribute($value): ?string
    {
        return $value ? Carbon::createFromFormat(detectDateFormat($value), $value)->format(config('project.date_format')) : null;
    }

    public function setScheduleAtAttribute($value): void
    {
        $this->attributes['schedule_at'] = $value ? Carbon::createFromFormat(config('project.date_format'), $value)->format('Y-m-d') : null;
    }

    /**
     * A message has many logs (emails sent from this message).
     */
    public function logs(): HasMany
    {
        return $this->hasMany(NewsletterLog::class);
    }

    /**
     * Scope to get messages that should be sent.
     */
    public function scopeScheduledToSend($query)
    {
        return $query->where('schedule_at', '<=', now());
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            0 => 'Pending',
            1 => 'Sent',
            2 => 'Failed',
            default => 'Unknown',
        };
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

