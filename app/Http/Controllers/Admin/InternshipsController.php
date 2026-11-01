<?php

namespace App\Http\Controllers\Admin;

use App\College;
use App\Internship;
use App\University;
use Illuminate\Http\Request;

class InternshipsController extends BaseCrudController
{
    protected function config(): array
    {
        return [
            'key'      => 'internship',
            'model'    => Internship::class,
            'route'    => 'admin.internships',
            'children' => ['registrations'],
            'with'     => ['college'],
            'fields'   => [
                ['name' => 'name', 'type' => 'text', 'required' => true, 'unique' => true],
                ['name' => 'duration_weeks', 'type' => 'number', 'rules' => ['integer', 'min:1', 'max:520']],
                ['name' => 'price', 'type' => 'number', 'required' => true, 'rules' => ['min:0', 'max:9999999']],
                ['name' => 'tax_percent', 'type' => 'number', 'required' => true, 'rules' => ['min:0', 'max:100']],
                ['name' => 'description', 'type' => 'textarea', 'index' => false],
                ['name' => 'is_active', 'type' => 'checkbox'],
            ],
        ];
    }

    public function create()
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'create');

        $colleges = College::with('university')->orderBy('name')->get();

        return view('admin.internships.form', [
            'cfg'     => $cfg,
            'record'  => null,
            'colleges' => $colleges,
        ]);
    }

    public function edit($id)
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'edit');

        $record = $cfg['model']::findOrFail($id);
        $colleges = College::with('university')->orderBy('name')->get();

        return view('admin.internships.form', [
            'cfg'     => $cfg,
            'record'  => $record,
            'colleges' => $colleges,
        ]);
    }

    public function index()
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'access');

        $query = $cfg['model']::with($cfg['with'] ?? []);

        if(!auth()->user()->isCollegeAdmin()) {
            if(request('college_id')) {
                $query->where('college_id', request('college_id'));
            }
            if(request('university_id')) {
                $query->whereHas('college', function($q) {
                    $q->where('university_id', request('university_id'));
                });
            }
        }

        $rows = $query->get();
        $universities = University::orderBy('name')->get();
        $colleges = College::with('university')->orderBy('name')->get();

        return view('admin.internships.index', compact('cfg', 'rows', 'universities', 'colleges'));
    }

    public function store(Request $request)
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'create');

        $data = $this->validated($request, $cfg);
        if(auth()->user()->isCollegeAdmin()) {
            $data['college_id'] = auth()->user()->college_id;
        }
        $cfg['model']::create($data);

        return redirect()->route($cfg['route'] . '.index')->with('message', trans('global.saved'));
    }

    public function update(Request $request, $id)
    {
        $cfg = $this->config();
        $this->authorizeAction($cfg, 'edit');

        $record = $cfg['model']::findOrFail($id);
        $data = $this->validated($request, $cfg, $record);
        if(auth()->user()->isCollegeAdmin()) {
            $data['college_id'] = auth()->user()->college_id;
        }
        $record->update($data);

        return redirect()->route($cfg['route'] . '.index')->with('message', trans('global.saved'));
    }
}
