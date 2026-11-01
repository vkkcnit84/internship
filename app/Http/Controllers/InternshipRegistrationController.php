<?php

namespace App\Http\Controllers;

use App\College;
use App\Http\Requests\StoreInternshipRegistrationRequest;
use App\Internship;
use App\InternshipRegistration;
use App\Role;
use App\Stream;
use App\StudentProfile;
use App\Subject;
use App\University;
use App\User;
use Illuminate\Support\Facades\DB;

class InternshipRegistrationController extends Controller
{
    public function create()
    {
        // A signed-in student who has already registered goes straight to their payment/status page.
        if (auth()->check() && ($existing = auth()->user()->internshipRegistrations()->latest('id')->first())) {
            return redirect()->route('internship.registration.show', $existing);
        }

        // After a validation error the page reloads with the old selections, so
        // the child dropdowns need their options rendered server-side.
        $colleges = old('university_id') ? College::where('university_id', old('university_id'))->orderBy('name')->get(['id', 'name', 'university_id']) : collect();
        $streams  = old('college_id') ? Stream::where('college_id', old('college_id'))->orderBy('name')->get(['id', 'name']) : collect();
        $subjects = old('stream_id') ? Subject::where('stream_id', old('stream_id'))->orderBy('name')->get(['id', 'name']) : collect();

        return view('internship.register', [
            'breadcrumb'      => 'Internship Registration',
            'internships'     => Internship::active()->orderBy('name')->get(['id', 'name', 'college_id', 'price', 'tax_percent', 'duration_weeks']),
            'universities'    => University::orderBy('name')->get(['id', 'name']),
            'colleges'        => $colleges,
            'streams'         => $streams,
            'subjects'        => $subjects,
            'genders'         => StudentProfile::GENDER_SELECT,
            'classSemesters'  => StudentProfile::classSemesterOptions(),
            'yearsOfStudy'    => StudentProfile::YEAR_OF_STUDY_SELECT,
            'academicSessions' => StudentProfile::academicSessionOptions(),
        ]);
    }

    public function store(StoreInternshipRegistrationRequest $request)
    {
        $internship = Internship::active()->findOrFail($request->input('internship_id'));

        $registration = DB::transaction(function () use ($request, $internship) {
            $user = User::create([
                'name'     => $request->input('name'),
                'email'    => $request->input('email'),
                'password' => $request->input('password'),
            ]);

            if ($studentRoleId = Role::where('title', 'Student')->value('id')) {
                $user->roles()->attach($studentRoleId);
            }

            $user->studentProfile()->create($request->only([
                'mobile', 'whatsapp', 'guardian_name', 'gender',
                'university_id', 'college_id', 'stream_id', 'subject_id',
                'class_semester', 'year_of_study', 'registration_number', 'roll_number', 'academic_session',
            ]));

            $total = $internship->getTotalAmount();
            $paymentMethod = $request->input('payment_method', InternshipRegistration::PAYMENT_METHOD_OFFLINE);

            return $user->internshipRegistrations()->create([
                'internship_id'   => $internship->id,
                'price'           => $internship->price,
                'tax_percent'     => $internship->tax_percent,
                'tax_amount'      => $internship->getTaxAmount(),
                'total_amount'    => $total,
                'payment_method'  => $paymentMethod,
                // A free programme has nothing to charge, so it activates immediately.
                'status'          => $total > 0 ? InternshipRegistration::STATUS_PENDING_PAYMENT : InternshipRegistration::STATUS_PAID,
                'paid_at'         => $total > 0 ? null : now(),
                // For offline payments, set approval status to pending until admin approves
                'approval_status' => $paymentMethod === InternshipRegistration::PAYMENT_METHOD_OFFLINE
                    ? InternshipRegistration::APPROVAL_PENDING
                    : InternshipRegistration::APPROVAL_APPROVED,
            ]);
        });

        auth()->login($registration->user);

        return redirect()->route('internship.registration.show', $registration);
    }
}
