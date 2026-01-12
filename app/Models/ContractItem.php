<?php
/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 17/10/24, 5:02 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractItem extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'contract_items';

    protected $orderable = [
        'id',
        'contract.code',
        'contract_revision.start_date',
        'product.name',
        'description',
        'remark',
        'amount',
    ];

    protected $filterable = [
        'id',
        'contract.code',
        'contract_revision.start_date',
        'product.name',
        'description',
        'remark',
        'amount',
    ];

    protected $fillable = [
        'contract_id',
        'contract_revision_id',
        'product_id',
        'description',
        'remark',
        'amount',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function contractRevision()
    {
        return $this->belongsTo(ContractRevision::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->where('type', 'service');
    }
}
