<?php

namespace Database\Seeders\Custom;

use App\Models\Language;
use App\Models\LanguageTerm;
use App\Models\Translation;
use Illuminate\Database\Seeder;

class LanguageTranslationTableSeeder extends Seeder
{
    public function run()
    {
        $terms = LanguageTerm::all();
        $languages = Language::all();
        $insert = [];

        foreach ($languages as $language) {
            // Load translations from lang/{locale}/cruds.php
            $cruds = $this->loadTranslations($language->locale, 'cruds');
            $customCruds = $this->loadTranslations($language->locale, 'custom-cruds');

            // Flatten nested arrays to dot notation
            $flattened = [];
            $this->flattenArray($cruds, '', $flattened);
            $this->flattenArray($customCruds, '', $flattened);

            // Create translation entries
            foreach ($flattened as $key => $value) {
                $term = collect($terms)->where('name', $key)->first();
                if ($term) {
                    $insert[] = [
                        'id' => null,
                        'language_id' => $language->id,
                        'language_term_id' => $term->id,
                        'translation' => $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        Translation::insert($insert);
    }

    private function loadTranslations($locale, $file)
    {
        $path = base_path("lang/{$locale}/{$file}.php");
        if (file_exists($path)) {
            return include $path;
        }

        return [];
    }

    private function flattenArray($array, $prefix, &$result)
    {
        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $this->flattenArray($value, $newKey, $result);
            } else {
                $result[$newKey] = $value;
            }
        }
    }
}
