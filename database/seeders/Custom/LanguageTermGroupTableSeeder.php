<?php

namespace Database\Seeders\Custom;

use App\Models\LanguageTermGroup;
use App\Services\LanguageService;
use Illuminate\Database\Seeder;

class LanguageTermGroupTableSeeder extends Seeder
{
    public function run()
    {
        LanguageTermGroup::insertOrIgnore(LanguageService::getGroups());
    }
}
