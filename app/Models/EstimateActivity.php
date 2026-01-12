<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateActivity extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'estimate_activities';

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'estimate.quo_number',
        'title',
        'description',
        'user.name',
    ];

    protected $orderable = [
        'id',
        'estimate.quo_number',
        'title',
        'description',
        'user.name',
        'is_active',
    ];

    protected $fillable = [
        'estimate_id',
        'title',
        'description',
        'user_id',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
