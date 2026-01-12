<?php

namespace Database\Seeders\Project;

use App\Models\PaymentMode;

use Illuminate\Database\Seeder;

class PaymentModeTableSeeder extends Seeder
{
    public function run()
    {
        $data = ['Cash','Cheque'];

        foreach ($data as $d) {
            PaymentMode::create(['name'=>$d,'active'=>1]);
        }
    }
}
