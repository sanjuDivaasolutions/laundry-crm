<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantQuota extends Model
{
    protected $fillable = ['tenant_id', 'quota_code', 'limit', 'period'];
}
