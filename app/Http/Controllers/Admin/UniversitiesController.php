<?php

namespace App\Http\Controllers\Admin;

use App\University;
use Symfony\Component\HttpFoundation\Response;

class UniversitiesController extends BaseCrudController
{
    public function index()
    {
        if(auth()->user()->isCollegeAdmin()) {
            abort(Response::HTTP_FORBIDDEN);
        }
        return parent::index();
    }

    protected function config(): array
    {
        return [
            'key'      => 'university',
            'model'    => University::class,
            'route'    => 'admin.universities',
            'children' => ['colleges', 'studentProfiles'],
            'fields'   => [
                ['name' => 'name', 'type' => 'text', 'required' => true, 'unique' => true],
            ],
        ];
    }
}
