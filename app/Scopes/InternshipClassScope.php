<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class InternshipClassScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (!request()->is('admin/*')) {
            return;
        }

        $user = auth()->user();

        if ($user && $user->isCollegeAdmin()) {
            $builder->whereHas('internship', function ($query) use ($user) {
                $query->where('college_id', $user->college_id);
            });
        }
    }
}
