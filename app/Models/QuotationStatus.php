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
 *  *  Last modified: 21/01/25, 6:08 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Attributes\StatusLabelAttribute;
use App\Support\HasAdvancedFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationStatus extends Model
{
    use HasAdvancedFilter, StatusLabelAttribute;

    public $appends = [
        'status_label',
    ];

    public const STATUS_SELECT = [
        [
            'label' => 'Draft',
            'value' => 'draft',
        ],
        [
            'label' => 'Confirmed',
            'value' => 'confirmed',
        ],
        [
            'label' => 'Cancelled',
            'value' => 'cancelled',
        ],
    ];

    protected array $orderable = [
        'id',
        'date',
        'active',
        'status',
        'remark',
    ];

    protected array $filterable = [
        'id',
        'date',
        'active',
        'status',
        'remark',
    ];

    protected $fillable = [
        'date',
        'active',
        'status',
        'remark',
        'quotation_id',
        'user_id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
