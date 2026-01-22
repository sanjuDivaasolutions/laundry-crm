<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantFeature extends Model
{
    protected $fillable = ['tenant_id', 'feature_code', 'enabled', 'expires_at'];
    protected $casts = ['enabled' => 'boolean', 'expires_at' => 'datetime'];
}
