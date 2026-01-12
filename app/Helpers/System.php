<?php

use Illuminate\Support\Carbon;

/*if (! function_exists('pfRound')) {
    function pfRound($value,$decimal=null){
        if(empty($decimal)) { $decimal = config('project.default_decimals',2); }
        return number_format((float)$value, $decimal, '.', '');
    }
}
if (! function_exists('pfMoneyRound')) {
    function pfMoneyRound($value) {
        $curr_code = config('project.default_currency_code','INR');
        $locale = config('project.default_locale','en_IN');
        $fmt = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return numfmt_format_currency($fmt, $value, $curr_code);
    }
}
if (! function_exists('getTotalObject')) {
    function getTotalObject($title,$text,$value) {
        return [
            'title' => $title,
            'text'  => $text,
            'value' => $value,
        ];
    }
}
if (! function_exists('generateCode')) {
    function generateCode($config) {
        return \cbagdawala\LaravelIdGenerator\IdGenerator::generate([
            'table' => $config['table'],
            'field' => $config['field'] ?? null,
            'length' => $config['length'] ?? 10,
            'prefix' =>$config['prefix'] ?? '',
            'reset_on_prefix_change'    =>  isset($config['reset_on_prefix_change']) && $config['reset_on_prefix_change'] ? $config['reset_on_prefix_change'] : true,
        ]);
    }
}*/
if (! function_exists('projectNowDate')) {
    function projectNowDate()
    {
        return Carbon::now()->format(config('project.date_format'));
    }
}
if (! function_exists('projectNowDateTime')) {
    function projectNowDateTime()
    {
        return Carbon::now()->format(config('project.datetime_format'));
    }
}
if (! function_exists('detectDateFormat')) {
    function detectDateFormat($value): string
    {
        return count(explode(' ', $value)) > 1 ? 'Y-m-d H:i:s' : 'Y-m-d';
    }
}

if (! function_exists('stringToArray')) {
    function stringToArray($arg): array
    {
        if (! is_array($arg)) {
            return json_decode($arg, true);
        }

        return $arg;
    }
}

if (! function_exists('ddr')) {
    function ddr($data, $toArray = false): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
    {
        if ($toArray) {
            return okResponse($data->toArray());
        }

        return okResponse($data);
    }
}
