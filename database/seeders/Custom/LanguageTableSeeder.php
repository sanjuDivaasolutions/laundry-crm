<?php

namespace Database\Seeders\Custom;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageTableSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id' => config('system.defaults.language.id', 1),
                'locale' => 'en',
                'active' => 1,
                'name' => 'English',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'locale' => 'de',
                'active' => 1,
                'name' => 'German',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        Language::insertOrIgnore($data);
    }
}
