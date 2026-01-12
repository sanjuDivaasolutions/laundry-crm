<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'active',
        'settings',
    ];

    protected $casts = [
        'active' => 'boolean',
        'settings' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }

    public function companies()
    {
        return $this->hasMany(\App\Models\Company::class);
    }
}
