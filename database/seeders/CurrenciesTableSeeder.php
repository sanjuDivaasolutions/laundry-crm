<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = [
            [
                'id' => 1,
                'name' => 'US Dollar',
                'code' => 'USD',
                'symbol' => '$',
                'rate' => 1.00,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add other default currencies if needed
        ];

        Currency::insert($currencies);
    }
}
