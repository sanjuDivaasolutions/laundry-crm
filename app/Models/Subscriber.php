<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscriber extends Model
{
    use HasFactory, HasAdvancedFilter;

    protected $fillable = ['name', 'email', 'active', 'company_id'];

    protected array $orderable = [
        'id',
        'name',
        'email',
        'active',
    ];

    protected array $filterable = [
        'id',
        'name',
        'email',
        'company_id',
        'active',
    ];

    /**
     * A subscriber has many logs (emails sent to them).
     */
    public function logs(): HasMany
    {
        return $this->hasMany(NewsletterLog::class);
    }
}

