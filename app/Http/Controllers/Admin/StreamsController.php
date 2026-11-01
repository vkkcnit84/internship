<?php

namespace App\Http\Controllers\Admin;

use App\College;
use App\Stream;

class StreamsController extends BaseCrudController
{
    protected function config(): array
    {
        return [
            'key'          => 'stream',
            'model'        => Stream::class,
            'route'        => 'admin.streams',
            'with'         => ['college.university'],
            'children'     => ['subjects', 'studentProfiles'],
            'unique_scope' => 'college_id',
            'fields'       => [
                ['name' => 'college_id', 'type' => 'select', 'model' => College::class, 'relation' => 'college', 'with' => ['university'], 'required' => true],
                ['name' => 'name', 'type' => 'text', 'required' => true, 'unique' => true],
            ],
        ];
    }
}
