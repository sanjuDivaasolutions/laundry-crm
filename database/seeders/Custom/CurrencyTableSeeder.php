<?php

namespace Database\Seeders\Custom;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencyTableSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id' => 1,
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'rate' => 1,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'code' => 'CNY',
                'name' => 'Yuan Renminbi',
                'symbol' => '¥',
                'rate' => 1,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Currency::insertOrIgnore($data);
    }
}
