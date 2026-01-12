<?php

namespace App\Scopes;

use App\Models\Fir;
use App\Models\GzFir;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Broker;

class FirBuyerScope implements Scope
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $user = auth()->user();
        $roles = $user->roles->pluck('id')->toArray();
        if(count(array_intersect($roles,config('system.auth.admin_role_id',[])))) {
            return;
        }
        $departmentIds = getUserDepartmentIds();
        $firs = Fir::query()->where('department_id', $departmentIds)->get();
        $gzFirs = GzFir::where('department_id', $departmentIds)->get();

        $buyerIds = array_unique(array_merge($firs->pluck('buyer_id')->toArray(), $gzFirs->pluck('buyer_id')->toArray()));
        $importerIds = array_unique($firs->pluck('importer_id')->toArray());

        $builder->where(function($q) use ($buyerIds, $importerIds) {
            $q->whereIn('id', $buyerIds);
            $q->orWhereIn('id', $importerIds);
        });
    }
}
