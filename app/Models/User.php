<?php
/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 07/01/25, 4:12 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasAdvancedFilter, Searchable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
        'language_id',
    ];

    protected $orderable = [
        'id',
        'name',
        'email',
    ];

    protected $filterable = [
        'id',
        'name',
        'email',
    ];

    protected $searchable = [
        'id',
        'name',
        'email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'language_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'settings'          => 'array',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function scopeNonAdmin()
    {
        return $this->whereNotIn('id', config('system.auth.admin_user_id'));
    }

    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] = Hash::needsRehash($input) ? Hash::make($input) : $input;
        }
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }



    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function getActiveLabelAttribute()
    {
        return $this->active ? "Active" : 'Inactive';
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('permissions.title', $permission);
            })
            ->exists();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->roles()
            ->where('roles.title', 'admin')
            ->exists() ||
           in_array($this->id, config('system.auth.admin_user_id', []));
    }

    /**
     * Check if user can access company data
     */
    public function canAccessCompany(int $companyId): bool
    {
        return $this->isAdmin() || $this->company_id === $companyId;
    }
}
