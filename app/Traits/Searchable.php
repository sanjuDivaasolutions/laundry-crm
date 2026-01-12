<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Searchable
{
    public function scopeSearch(Builder $builder, string $term = '')
    {
        abort_if(! $this->filterable && ! $this->searchable, 400, 'Please define the searchable property.');

        $searchables = $this->filterable;
        if ($this->searchable) {
            $searchables = $this->searchable;
        }

        if (empty($searchables)) {
            return $builder;
        }
        $builder->where(function ($q) use ($searchables, $term) {
            foreach ($searchables as $s) {
                if (str_contains($s, '.')) {
                    $relation = Str::beforeLast($s, '.');
                    $column = Str::afterLast($s, '.');
                    $q->orWhereRelation($relation, $column, 'like', $this->getTypeString($term));

                    continue;
                }
                $q->orWhere($s, 'like', $this->getTypeString($term));
            }
        });

        return $builder;
    }

    private function getTypeString($term)
    {
        $type = config('system.query.search.type', 'anywhere');
        if ($type == 'start') {
            return "$term%";
        } elseif ($type == 'end') {
            return "%$term";
        } else {
            return "%$term%";
        }
    }
}
