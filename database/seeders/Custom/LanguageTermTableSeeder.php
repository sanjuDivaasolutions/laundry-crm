<?php

namespace Database\Seeders\Custom;

use App\Models\LanguageTerm;
use App\Models\LanguageTermGroup;
use App\Services\LanguageService;
use Illuminate\Database\Seeder;

class LanguageTermTableSeeder extends Seeder
{
    public function run()
    {
        $data = LanguageService::getCrudTerms();
        $languageTermGroups = LanguageTermGroup::all();
        $insert = [];
        if (! $data) {
            return;
        }
        foreach ($data as $key => $fields) {
            $group = collect($languageTermGroups)->where('name', $key)->first();
            if ($group) {
                foreach ($fields as $k => $value) {
                    $insert[] = [
                        'id' => null,
                        'language_term_group_id' => $group->id,
                        'active' => 1,
                        'name' => $k,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        LanguageTerm::insert($insert);
    }
}
