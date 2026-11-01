<?php

namespace App\Http\Controllers\Admin;

use App\College;
use App\Stream;
use App\Subject;

class SubjectsController extends BaseCrudController
{
    public function index()
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'access');

        $query = $cfg['model']::with($cfg['with'] ?? []);

        // Filter by college/stream for both college admins and super admin
        if(!auth()->user()->isCollegeAdmin()) {
            if(request('college_id')) {
                $query->whereHas('stream', function($q) {
                    $q->where('college_id', request('college_id'));
                });
            }
            if(request('stream_id')) {
                $query->where('stream_id', request('stream_id'));
            }
        }

        $rows = $query->get();
        $colleges = College::with('university')->orderBy('name')->get();
        $streams = Stream::with('college.university')->orderBy('name')->get();

        return view('admin.subjects.index', compact('cfg', 'rows', 'colleges', 'streams'));
    }

    protected function config(): array
    {
        return [
            'key'          => 'subject',
            'model'        => Subject::class,
            'route'        => 'admin.subjects',
            'with'         => ['stream.college.university'],
            'children'     => ['studentProfiles'],
            'unique_scope' => 'stream_id',
            'fields'       => [
                ['name' => 'stream_id', 'type' => 'select', 'model' => Stream::class, 'relation' => 'stream', 'with' => ['college.university'], 'required' => true],
                ['name' => 'name', 'type' => 'text', 'required' => true, 'unique' => true],
            ],
        ];
    }
}
