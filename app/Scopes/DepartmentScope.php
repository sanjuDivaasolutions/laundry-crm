<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DepartmentScope implements Scope
{
    public mixed $prefix = null;

    public function __construct($prefix = null)
    {
        $this->prefix = $prefix;
    }
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $column = $this->prefix ? $this->prefix.'.department_id' : 'department_id';
        $builder->whereIn($column, getUserDepartmentIds());
        /*$builder->where(function($q) {
            $q->orWhereIn('department_id', getUserDepartmentIds());
            $q->orWhere('user_id', auth()->id());
        });*/
    }


}
