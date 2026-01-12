<?php

namespace App\Services;

use Illuminate\Support\Str;

class QueryService
{
    public static function search($class, $field, $q, $idValue = 'value', $labelValue = 'label', $scopes = [], $additional = [], $filterWheres = [], $relations = [], $appends = [], $unsets = [], $withoutScopes = [])
    {
        $obj = $class::query();

        $trimmedQ = trim((string) $q);
        //ranked relevance-based search for all models
        if ($trimmedQ !== '') {
            $qLower = mb_strtolower($trimmedQ);
            // where clause: only fetch related matches
            $obj->whereRaw("LOWER($field) LIKE ?", ["%{$qLower}%"]);
            // rank results by how closely they match
            $obj->orderByRaw(
                "CASE
                    WHEN LOWER($field) LIKE ? THEN 0         -- starts with match
                    WHEN LOWER($field) LIKE ? THEN 1         -- word boundary match
                    WHEN LOWER($field) LIKE ? THEN 2         -- contains match
                    ELSE 3
                END, LENGTH($field), LOWER($field) ASC",
                [
                    "{$qLower}%",
                    "% {$qLower}%",
                    "%{$qLower}%",
                ]
            );
         } else {
            $obj->orderBy($field, 'asc');
        }

        if ($relations) {
            $obj->with($relations);
        }

        foreach ($scopes as $s) {
            if (is_array($s)) {
                $obj->{$s['method']}(...$s['args']);
            } else {
                $obj->{$s}();
            }
        }

        if ($withoutScopes) {
            foreach ($withoutScopes as $s) {
                $obj->withoutGlobalScope($s);
            }
        }

        if ($filterWheres) {
            foreach ($filterWheres as $where) {
                if (count(explode('.', $where['field'])) > 1) {
                    $wRelation = Str::beforeLast($where['field'], '.');
                    $wField = Str::afterLast($where['field'], '.');
                    $obj->whereRelation(Str::camel($wRelation), $wField, $where['operator'], $where['value']);
                } else {
                    $obj->where($where['field'], $where['operator'], $where['value']);
                }
            }
        }

        $select = array_merge(["id as {$idValue}", "{$field} as {$labelValue}"], $additional);
        $limit = (int) config('system.query.limit', 10);

        $results = $obj
            ->limit($limit)
            ->get($select);

        if ($appends) {
            foreach ($results as $model) {
                if (method_exists($model, 'append')) {
                    $model->append($appends);
                }
            }
        }

        $arr = $results->toArray();

        if ($arr && $unsets) {
            foreach ($arr as &$item) {
                foreach ($unsets as $unset) {
                    unset($item[$unset]);
                }
            }
            unset($item);
        }

        return $arr;
    }

    public static function searchArray($arr, $q, $idValue = 'value', $labelValue = 'label')
    {
        $collection = collect($arr)
            ->filter(function ($item) use ($q) {
                return isset($item['label'])
                    && str_contains(mb_strtolower((string) $item['label']), mb_strtolower((string) $q));
            })
            ->sortBy(function ($item) {
                return isset($item['label']) ? mb_strtolower((string) $item['label']) : '';
            })
            ->values();

        $limit = (int) config('system.query.limit', 10);

        return $collection->take($limit)->map(function ($item) use ($idValue, $labelValue) {
            return [
                $idValue => $item['value'] ?? null,
                $labelValue => $item['label'] ?? null,
            ];
        })->values()->toArray();
    }
}
