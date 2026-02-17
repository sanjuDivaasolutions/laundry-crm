<?php

namespace App\Services;

use cbagdawala\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\DB;

class UtilityService
{
    public static function generateCode($config)
    {
        // IdGenerator uses information_schema which is not available in SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            return self::generateCodeFallback($config);
        }

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

    protected static function generateCodeFallback(array $config): string
    {
        $table = $config['table'];
        $field = $config['field'] ?? 'code';
        $prefix = $config['prefix'] ?? '';
        $length = $config['length'] ?? 10;

        $last = DB::table($table)
            ->where($field, 'like', $prefix.'%')
            ->orderByDesc($field)
            ->value($field);

        if ($last) {
            $number = (int) str_replace($prefix, '', $last) + 1;
        } else {
            $number = 1;
        }

        return $prefix.str_pad((string) $number, $length - strlen($prefix), '0', STR_PAD_LEFT);
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
