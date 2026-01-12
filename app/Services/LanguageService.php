<?php
/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 16/01/25, 10:33 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Services;

use App\Models\Language;
use App\Models\LanguageTerm;
use App\Models\LanguageTermGroup;
use App\Models\Translation;
use Illuminate\Support\Facades\DB;

class LanguageService
{

    public static function getTerms(): array
    {
        return [
            "dashboard"     => "Dashboard",
            "settings"      => "Settings",
            "signIn"        => "Sign-in",
            "signUp"        => "Sign-up",
            "passwordReset" => "Password Reset",
            "error404"      => "Error 404",
            "error500"      => "Error 500",
        ];
    }

    public static function getCrudTerms(): array
    {
        $cruds = trans('cruds');
        $customCruds = trans('custom-cruds');
        $usedField = [];
        $crudTerms = self::getArrayTerms($cruds, $usedField);
        if (is_array($customCruds)) {
            $customCrudTerms = self::getArrayTerms($customCruds, $usedField);
            foreach ($customCrudTerms as $groupKey => $group) {
                foreach ($group as $key => $item) {
                    if (!isset($crudTerms[$groupKey]) || !is_array($crudTerms[$groupKey])) {
                        $crudTerms[$groupKey] = [];
                    }
                    $crudTerms[$groupKey][$key] = $item;
                }
            }
        }
        return $crudTerms;
    }

    private static function getArrayTerms($cruds, &$usedField)
    {
        $terms = [];
        $fieldTerms = [];
        $fieldGroup = config('system.defaults.language_group.name', 'general');
        foreach ($cruds as $k => $c) {
            if (isset($c['title']) && trim($c['title'])) {
                $terms[$k][$k . '.title'] = $c['title'];
            }
            $fields = (isset($c['fields']) && is_array($c['fields'])) ? $c['fields'] : [];
            foreach ($fields as $fk => $f) {
                $idx = $fieldGroup . '.fields.' . $fk;
                if (in_array($idx, $usedField)) continue;
                if (!trim($f)) continue;
                $fieldTerms[$fieldGroup][$idx] = $f;
                $usedField[] = $idx;
            }
        }
        return array_merge($terms, $fieldTerms);
    }

    public static function getGroups(): array
    {
        $cruds = trans('cruds');
        $customCruds = trans('custom-cruds');
        $groups = [];
        $addedGroups = [];
        if ($cruds) {
            foreach ($cruds as $key => $value) {
                if (in_array($key, $addedGroups)) continue;
                $groups[] = [
                    'id'         => null,
                    'name'       => $key,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $addedGroups[] = $key;
            }
        }
        if ($customCruds) {
            foreach ($customCruds as $key => $value) {
                if (in_array($key, $addedGroups)) continue;
                $groups[] = [
                    'id'         => null,
                    'name'       => $key,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $addedGroups[] = $key;
            }
        }
        return $groups;
    }

    public static function copyTranslations($obj): void
    {
        $primaryLanguage = Language::query()
            ->with([
                'translations' => function ($q) {
                    $q->orderBy('id');
                }
            ])
            ->find(config('system.defaults.language.id', 1));
        if ($primaryLanguage) {
            $translations = $primaryLanguage->translations;
            foreach ($translations as $translation) {
                $obj->translations()->create([
                    'language_id'      => $primaryLanguage->id,
                    'translation'      => $translation->translation,
                    'language_term_id' => $translation->language_term_id
                ]);
            }
        }
    }

    public static function updateLanguageData(): void
    {
        //Clear Language Data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Translation::truncate();
        LanguageTerm::truncate();
        LanguageTermGroup::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $terms = self::getCrudTerms();

        self::updateGroups();
        self::updateTerms($terms);
        self::updateTranslations($terms);
    }

    public static function updateTerms($terms)
    {
        $dbTerms = LanguageTerm::all();

        foreach ($terms as $groupKey => $group) {
            foreach ($group as $key => $term) {
                $dbTerm = $dbTerms->where('name', $key)->first();
                if (!$dbTerm) {
                    $group = LanguageTermGroup::where('name', $groupKey)->first();
                    if ($group) {
                        LanguageTerm::create([
                            'language_term_group_id' => $group->id,
                            'name'                   => $key,
                            'active'                 => 1,
                        ]);
                    }
                }
            }
        }
    }

    public static function updateTranslations($terms)
    {
        $dbTerms = LanguageTerm::all();

        //Create translations for all languages if not exists
        $languages = Language::all();
        foreach ($languages as $language) {
            $translations = $language->translations;
            foreach ($terms as $groupKey => $group) {
                foreach ($group as $key => $term) {
                    $dbTerm = $dbTerms->where('name', $key)->first();
                    if ($dbTerm) {
                        $translation = $translations->where('language_term_id', $dbTerm->id)->first();
                        if (!$translation) {
                            $language->translations()->create([
                                'language_term_id' => $dbTerm->id,
                                'translation'      => $term,
                            ]);
                        }
                    }
                }
            }
        }
    }

    public static function updateGroups()
    {
        $groups = self::getGroups();
        $dbGroups = LanguageTermGroup::all();

        foreach ($groups as $group) {
            $dbGroup = $dbGroups->where('name', $group['name'])->first();
            if (!$dbGroup) {
                LanguageTermGroup::create($group);
            }
        }
    }

}
