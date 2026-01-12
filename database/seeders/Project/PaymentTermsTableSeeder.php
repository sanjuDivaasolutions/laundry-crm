<?php

namespace Database\Seeders\Project;

use App\Models\PaymentTerm;
use Illuminate\Database\Seeder;

class PaymentTermsTableSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'    => 1,
                'name' => '21 Days',
                'days' => 21,
                'created_at'=>now(),
                'updated_at'=>now(),
            ],
        ];
        PaymentTerm::insert($data);
    }
}
