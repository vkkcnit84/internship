<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CourseScope implements Scope
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
        if(!request()->is('admin/*')) {
            return;
        }

        $user = auth()->user();

        if($user->isCollegeAdmin()) {
            $builder->where('college_id', $user->college_id);
        } elseif($user->isInstitution()) {
            $builder->whereHas('institution', function($query) use ($user) {
                $query->whereId($user->institution_id);
            });
        }
    }
}