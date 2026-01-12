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
 *  *  Last modified: 29/01/25, 10:50 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Attributes\DateAttribute;
use App\Support\HasAdvancedFilter;
use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Expense extends Model implements HasMedia
{
    use HasAdvancedFilter, DateAttribute, CompanyScopeTrait, InteractsWithMedia;

    protected $appends = ['is_taxable_label'];

    protected $fillable = [
        'code',
        'description',
        'invoice_number',
        'sub_total',
        'tax_rate',
        'tax_total',
        'grand_total',
        'is_taxable',
        'date',
        'expense_type_id',
        'payment_mode_id',
        'company_id',
        'state_id',
        'user_id',
    ];

    protected array $orderable = [
        'id',
        'code',
        'description',
        'invoice_number',
        'sub_total',
        'tax_rate',
        'tax_total',
        'grand_total',
        'is_taxable',
        'date',
        'company.name',
        'expense_type.name',
        'payment_mode.name',
        'user.name',
    ];

    protected array $filterable = [
        'id',
        'code',
        'description',
        'invoice_number',
        'sub_total',
        'tax_rate',
        'tax_total',
        'grand_total',
        'is_taxable',
        'date',
        'expense_type_id',
        'payment_mode_id',
        'company_id',
        'user_id',
    ];

    protected $casts = [
        'date'       => 'datetime',
        'is_taxable' => 'boolean',
    ];

    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class);
    }

    public function paymentMode(): BelongsTo
    {
        return $this->belongsTo(PaymentMode::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function taxes(): MorphMany
    {
        return $this->morphMany(OrderTaxDetail::class, 'taxable')->orderBy('priority');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAttachmentAttribute()
    {
        return $this->getMedia('expense_attachment')->map(function ($item) {
            $media = $item->toArray();
            $media['url'] = $item->getUrl();
            $media['path'] = $item->getPath();

            return $media;
        });
    }

    public function getIsTaxableLabelAttribute(): string
    {
        return $this->is_taxable ? "Yes" : "No";
    }
}
