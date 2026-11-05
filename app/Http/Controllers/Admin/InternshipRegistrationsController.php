<?php

namespace App\Http\Controllers\Admin;

use App\College;
use App\Http\Controllers\Controller;
use App\InternshipRegistration;
use App\University;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class InternshipRegistrationsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('internship_registration_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = InternshipRegistration::with([
            'user.studentProfile.university',
            'user.studentProfile.college',
            'user.studentProfile.stream',
            'user.studentProfile.subject',
            'internship',
        ]);

        // Filter by college for college admins
        if(auth()->user()->isCollegeAdmin()) {
            // Already filtered by InternshipRegistrationScope
        } else {
            // Filter by university/college for super admin
            if(request('college_id')) {
                $query->whereHas('studentProfile', function($q) {
                    $q->where('college_id', request('college_id'));
                });
            }
            if(request('university_id')) {
                $query->whereHas('studentProfile', function($q) {
                    $q->where('university_id', request('university_id'));
                });
            }
        }

        $registrations = $query->get();
        $universities = University::orderBy('name')->get();
        $colleges = College::with('university')->orderBy('name')->get();

        return view('admin.internship_registrations.index', compact('registrations', 'universities', 'colleges'));
    }

    public function consentLetter(InternshipRegistration $registration)
    {
        return $this->letter($registration, 'admin.student.welcome_letter');
    }

    public function acceptanceLetter(InternshipRegistration $registration)
    {
        return $this->letter($registration, 'admin.student.acceptance_letter');
    }

    /**
     * Shows a student's letter to an admin; reuses the views the student portal renders.
     */
    private function letter(InternshipRegistration $registration, $view)
    {
        abort_if(Gate::denies('internship_registration_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $registration->load('internship', 'user.studentProfile.university', 'user.studentProfile.college', 'user.studentProfile.stream', 'user.studentProfile.subject');

        return view($view, [
            'user'          => $registration->user,
            'profile'       => $registration->user->studentProfile,
            'registrations' => collect([$registration]),
        ]);
    }

    public function approve(InternshipRegistration $registration)
    {
        abort_if(Gate::denies('internship_registration_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $registration->update([
            'approval_status' => InternshipRegistration::APPROVAL_APPROVED,
        ]);

        return back()->with('status', 'Registration approved successfully.');
    }

    public function reject(InternshipRegistration $registration)
    {
        abort_if(Gate::denies('internship_registration_reject'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $registration->update([
            'approval_status' => InternshipRegistration::APPROVAL_REJECTED,
        ]);

        return back()->with('status', 'Registration rejected.');
    }

    public function markPaid(InternshipRegistration $registration)
    {
        abort_if(Gate::denies('internship_registration_mark_paid'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $registration->update([
            'status' => InternshipRegistration::STATUS_PAID,
            'paid_at' => now(),
        ]);

        return back()->with('status', 'Registration marked as paid.');
    }

    public function markUnpaid(InternshipRegistration $registration)
    {
        abort_if(Gate::denies('internship_registration_mark_paid'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $registration->update([
            'status' => InternshipRegistration::STATUS_PENDING_PAYMENT,
            'paid_at' => null,
        ]);

        return back()->with('status', 'Registration marked as unpaid.');
    }
}
