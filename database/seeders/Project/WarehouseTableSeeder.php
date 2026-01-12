<?php

namespace Database\Seeders\Project;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseTableSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'    => 1,
                'code'  => 'WH-000001',
                'name' => 'Main Warehouse',
                'address_1' => '1234 Main Street',
                'address_2' => 'Suite 200',
                'country_id' => 1,
                'state_id' => 1,
                'city_id' => 1,
                'postal_code' => '12345',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            
        ];
        Warehouse::insert($data);
    }
}
