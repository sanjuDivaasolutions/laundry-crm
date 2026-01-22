<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUsage extends Model
{
    protected $table = 'tenant_usage';
    protected $fillable = ['tenant_id', 'quota_code', 'current_usage', 'reset_at'];
    protected $casts = ['reset_at' => 'datetime'];
}
