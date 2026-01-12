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
 *  *  Last modified: 18/11/24, 6:17 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasAdvancedFilter, HasFactory;

    public $table = 'product_prices';

    protected $appends = [
        'sale_price_text',
        'purchase_price_text',
        'lowest_sale_price_text',
    ];

    protected $orderable = [
        'id',
        'product.name',
        'unit.name',
        'purchase_price',
        'sale_price',
        'lowest_sale_price',
    ];

    protected $filterable = [
        'id',
        'product.name',
        'unit.name',
        'purchase_price',
        'sale_price',
        'lowest_sale_price',
    ];

    protected $fillable = [
        'product_id',
        'unit_id',
        'purchase_price',
        'sale_price',
        'lowest_sale_price',
        'created_at',
        'updated_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function getSalePriceTextAttribute()
    {
        $currencySign = config('system.defaults.currency.symbol', '$');
        return $this->sale_price ? $currencySign . number_format($this->sale_price, 2) : '';
    }

    public function getPurchasePriceTextAttribute()
    {
        $currencySign = config('system.defaults.currency.symbol', '$');
        return $this->purchase_price ? $currencySign . number_format($this->purchase_price, 2) : '';
    }

    public function getLowestSalePriceTextAttribute()
    {
        $currencySign = config('system.defaults.currency.symbol', '$');
        return $this->lowest_sale_price ? $currencySign . number_format($this->lowest_sale_price, 2) : '';
    }
}
