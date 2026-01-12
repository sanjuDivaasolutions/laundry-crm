<?php

namespace App\Services;

use cbagdawala\LaravelIdGenerator\IdGenerator;

class UtilityService
{
    public static function generateCode($config)
    {
        $c = [
            'table' => $config['table'],
            'field' => $config['field'] ?? null,
            'length' => $config['length'] ?? 10,
            'prefix' => $config['prefix'] ?? '',
            'where' => $config['where'] ?? null,
            'reset_on_prefix_change' => isset($config['reset_on_prefix_change']) ? $config['reset_on_prefix_change'] : true,
        ];
        if (isset($c['where']) && ! $c['where']) {
            unset($c['where']);
        }

        return IdGenerator::generate($c);
    }

    public static function dsRound($value, $decimal = null)
    {
        if (empty($decimal)) {
            $decimal = config('project.default_decimals', 2);
        }

        /*$formatter = new NumberFormatter(config('project.default_locale','en_US'), NumberFormatter::DECIMAL_ALWAYS_SHOWN);
        return numfmt_format($formatter,(float)$value);*/
        return number_format((float) $value, $decimal, '.', '');
    }

    /*public static function dsMoneyRound($value,$decimal=null,$curr_sign='$') {
        $curr_code = config('project.default_currency_code','USD');
        $locale = config('project.default_locale','en_US');
        $fmt = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return numfmt_format_currency($fmt, $value, $curr_code);
    }*/

    public static function dsMoneyRound($value, $code = 'USD', $locale = 'en_US')
    {
        if (! $code) {
            $curr_code = config('project.default_currency_code', 'USD');
        } else {
            $curr_code = $code;
        }
        if (! $locale) {
            $locale = config('project.default_locale', 'en_US');
        }
        $fmt = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        return numfmt_format_currency($fmt, $value, $curr_code);
    }

    public static function flatCall($data_arr, $data_arr_call)
    {
        $current = $data_arr;
        foreach ($data_arr_call as $key) {
            $current = $current[$key] ?? '';
            if (is_array($current)) {
                $current = implode(', ', $current);
            }
        }

        return $current;
    }
}
