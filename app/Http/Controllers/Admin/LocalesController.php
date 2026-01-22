<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\Request;

class LocalesController extends Controller
{
    public function languages()
    {
        return okResponse([
            'languages' => Language::orderBy('name')->get(),
            'locale' => app()->getLocale(),
        ]);
    }

    public function messages(Request $request)
    {
        /*$user = adminAuth()->user();
        $languageId = $user->language_id ?: 1;
        $language = Language::query()->find($languageId);
        $locale = $language->locale ?: config('system.defaults.language.locale', 'en');*/
        if ($request->has('locale')) {
            $locale = $request->get('locale');
        } else {
            $user = adminAuth()->user();
            if ($user && $user->language) {
                $locale = $user->language->locale;
            } else {
                $locale = app()->getLocale();
            }
        }
        
        $translationData = Translation::query()
            ->whereHas('language', function ($query) use ($locale) {
                $query->where('locale', $locale);
            })
            ->with(['language', 'languageTerm'])
            ->get();

        $translations = [];
        foreach ($translationData as $t) {
            $translations[$t->language->locale][$t->languageTerm->name] = $t->translation;
        }

        return okResponse([
            'messages' => $translations,
        ]);

        /*return response()->json([
            'auth'       => trans('auth'),
            'cruds'      => trans('cruds'),
            'global'     => trans('global'),
            'pagination' => trans('pagination'),
            'panel'      => trans('panel'),
            'passwords'  => trans('passwords'),
            'validation' => trans('validation'),
        ]);*/
    }
}
