<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InternshipRegistrationScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if(!request()->is('admin/*')) {
            return;
        }

        $user = auth()->user();

        if($user->isCollegeAdmin()) {
            $builder->whereHas('studentProfile', function($q) use ($user) {
                $q->where('college_id', $user->college_id);
            });
        }
    }
}
