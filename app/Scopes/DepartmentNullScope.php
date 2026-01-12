<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DepartmentNullScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where(function ($q) {
            $q->orWhereIn('department_id', getUserDepartmentIds());
            $q->orWhere('department_id', '=', null);
        });
    }
}
