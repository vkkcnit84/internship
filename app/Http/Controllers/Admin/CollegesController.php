<?php

namespace App\Http\Controllers\Admin;

use App\College;
use App\University;
use Symfony\Component\HttpFoundation\Response;

class CollegesController extends BaseCrudController
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
            'key'          => 'college',
            'model'        => College::class,
            'route'        => 'admin.colleges',
            'with'         => ['university'],
            'children'     => ['streams', 'studentProfiles'],
            'unique_scope' => 'university_id',
            'fields'       => [
                ['name' => 'university_id', 'type' => 'select', 'model' => University::class, 'relation' => 'university', 'required' => true],
                ['name' => 'name', 'type' => 'text', 'required' => true, 'unique' => true],
                ['name' => 'logo', 'type' => 'image', 'dir' => 'colleges'],
            ],
        ];
    }
}
