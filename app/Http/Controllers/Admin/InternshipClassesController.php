<?php

namespace App\Http\Controllers\Admin;

use App\College;
use App\Http\Controllers\Controller;
use App\Internship;
use App\InternshipClass;
use App\University;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InternshipClassesController extends Controller
{
    public function index()
    {
        $this->authorizeAction('access');

        $query = InternshipClass::with('internship.college.university')->orderBy('start_date', 'desc');

        if (!auth()->user()->isCollegeAdmin()) {
            if (request('college_id')) {
                $query->whereHas('internship', function ($q) {
                    $q->where('college_id', request('college_id'));
                });
            }
            if (request('university_id')) {
                $query->whereHas('internship.college', function ($q) {
                    $q->where('university_id', request('university_id'));
                });
            }
        }

        return view('admin.internship_classes.index', [
            'classes'      => $query->get(),
            'universities' => University::orderBy('name')->get(),
            'colleges'     => College::with('university')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $this->authorizeAction('create');

        return view('admin.internship_classes.form', $this->formData(null));
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');

        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        InternshipClass::create($data);

        return redirect()->route('admin.internship-classes.index')->with('message', trans('global.saved'));
    }

    public function show($id)
    {
        $this->authorizeAction('show');

        $class = InternshipClass::with('internship.college.university', 'creator')->findOrFail($id);

        return view('admin.internship_classes.show', [
            'class'    => $class,
            'upcoming' => $class->occurrences(null, 10),
            'recipients' => $class->whatsappRecipients(),
            'groupMessage' => $class->whatsappMessage(),
        ]);
    }

    public function edit($id)
    {
        $this->authorizeAction('edit');

        return view('admin.internship_classes.form', $this->formData(InternshipClass::findOrFail($id)));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAction('edit');

        $class = InternshipClass::findOrFail($id);
        $class->update($this->validated($request));

        return redirect()->route('admin.internship-classes.index')->with('message', trans('global.saved'));
    }

    public function destroy($id)
    {
        $this->authorizeAction('delete');

        InternshipClass::findOrFail($id)->delete();

        return back()->with('message', trans('global.deleted'));
    }

    private function authorizeAction(string $action): void
    {
        abort_if(Gate::denies('internship_class_' . $action), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function formData(?InternshipClass $class): array
    {
        return [
            'class'       => $class,
            'internships' => Internship::with('college.university')->orderBy('name')->get(),
            'colleges'    => College::with('university')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'internship_id' => ['required', 'integer', function ($attribute, $value, $fail) {
                // Internship has a college-admin scope, so another college's internship is "not found".
                if (!Internship::whereKey($value)->exists()) {
                    $fail('Please choose a valid internship.');
                }
            }],
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:5000',
            'mode'           => 'required|in:' . implode(',', array_keys(InternshipClass::MODE_SELECT)),
            'meeting_link'   => 'nullable|url|max:500',
            'venue'          => 'nullable|string|max:255',
            'recurrence'     => 'required|in:' . implode(',', array_keys(InternshipClass::RECURRENCE_SELECT)),
            'days_of_week'   => 'required_if:recurrence,weekly|nullable|array',
            'days_of_week.*' => 'integer|between:1,7',
            'start_date'     => 'required|date',
            'end_date'       => 'required_unless:recurrence,none|nullable|date|after_or_equal:start_date',
            'start_time'     => 'required|date_format:H:i',
            'end_time'       => 'required|date_format:H:i|after:start_time',
            'is_active'      => 'boolean',
        ]);

        if ($data['recurrence'] === 'none') {
            $data['end_date'] = $data['start_date'];
        }

        $data['days_of_week'] = $data['recurrence'] === 'weekly'
            ? implode(',', collect($data['days_of_week'])->map('intval')->unique()->sort()->values()->all())
            : null;

        $data['is_active'] = $request->boolean('is_active');

        $probe = new InternshipClass($data);
        if (!$probe->occurrences($probe->start_date, 1)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'days_of_week' => 'No class falls on the chosen days between the start and end dates.',
            ]);
        }

        return $data;
    }
}
