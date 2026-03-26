<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SchoolScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $currentSchool = app()->bound('current_school') ? app('current_school') : null;

        if (! $currentSchool) {
            return;
        }

        $builder->where($model->getTable() . '.school_id', $currentSchool->id);
    }
}

