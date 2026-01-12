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
 *  *  Last modified: 04/12/25, 11:10 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'company_id',
        'session_number',
        'status',
        'opened_at',
        'closed_at',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_sales',
        'total_transactions',
        'total_cash_sales',
        'total_card_sales',
        'total_other_sales',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_cash_sales' => 'decimal:2',
        'total_card_sales' => 'decimal:2',
        'total_other_sales' => 'decimal:2',
    ];

    /**
     * Get the user (cashier) for this session
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the warehouse for this session
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the company for this session
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all sales invoices for this session
     */
    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }

    /**
     * Scope to get only open sessions
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope to get only closed sessions
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Check if session is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if session is closed
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Get session duration in hours
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->opened_at) {
            return null;
        }

        $end = $this->closed_at ?? now();
        $diff = $this->opened_at->diff($end);

        if ($diff->h > 0) {
            return $diff->h . 'h ' . $diff->i . 'm';
        }
        return $diff->i . ' min';
    }

    /**
     * Generate unique session number
     */
    public static function generateSessionNumber(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'PS-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
