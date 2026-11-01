<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Course;
use App\Enrollment;
use App\Internship;
use App\InternshipRegistration;
use App\Payment;
use App\StudentProfile;

class HomeController
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isStudentOnly()) {
            return redirect()->route('admin.student.profile');
        }
        $stats = [];
        $recent_registrations = [];
        $payment_summary = [];

        if ($user->isAdmin()) {
            // Admin stats
            $stats = [
                'total_users' => User::count(),
                'total_institutions' => \App\Institution::count(),
                'total_courses' => Course::count(),
                'total_enrollments' => Enrollment::count(),
                'total_internships' => Internship::count(),
                'total_registrations' => InternshipRegistration::count(),
                'total_payments' => Payment::count(),
                'total_paid' => InternshipRegistration::where('status', InternshipRegistration::STATUS_PAID)->count(),
                'total_pending' => InternshipRegistration::where('status', InternshipRegistration::STATUS_PENDING_PAYMENT)->count(),
                'revenue' => Payment::where('status', 'captured')->sum('amount_paise') / 100,
            ];

            // Recent registrations
            $recent_registrations = InternshipRegistration::with(['user', 'internship'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            // Payment statistics
            $payment_summary = [
                'total_captured' => Payment::where('status', 'captured')->count(),
                'total_pending' => Payment::where('status', 'pending')->count(),
                'total_failed' => Payment::where('status', 'failed')->count(),
            ];
        } else if ($user->isInstitution()) {
            // Institution user stats
            $institution_id = $user->institution_id;

            $stats = [
                'total_users' => User::where('institution_id', $institution_id)->count(),
                'total_courses' => Course::where('institution_id', $institution_id)->count(),
                'total_enrollments' => Enrollment::whereHas('course', function ($q) use ($institution_id) {
                    $q->where('institution_id', $institution_id);
                })->count(),
                'enrollment_accepted' => Enrollment::whereHas('course', function ($q) use ($institution_id) {
                    $q->where('institution_id', $institution_id);
                })->where('status', 'accepted')->count(),
                'enrollment_pending' => Enrollment::whereHas('course', function ($q) use ($institution_id) {
                    $q->where('institution_id', $institution_id);
                })->where('status', 'awaiting')->count(),
            ];
        } else if ($user->college_id) {
            // College admin user stats - see only students and registrations from their college
            $college_id = $user->college_id;
            $college = \App\College::with('university')->find($college_id);

            // Get all student profile IDs for this college
            $studentProfileIds = StudentProfile::where('college_id', $college_id)->pluck('id');

            $stats = [
                'total_students' => $studentProfileIds->count(),
                'total_registrations' => InternshipRegistration::whereIn('student_profile_id', $studentProfileIds)->count(),
                'registrations_paid' => InternshipRegistration::where('status', InternshipRegistration::STATUS_PAID)
                    ->whereIn('student_profile_id', $studentProfileIds)->count(),
                'registrations_pending' => InternshipRegistration::where('status', InternshipRegistration::STATUS_PENDING_PAYMENT)
                    ->whereIn('student_profile_id', $studentProfileIds)->count(),
                'college_name' => $college->name,
                'university_name' => $college && $college->university ? $college->university->name : 'University',
            ];

            // Recent registrations from this college
            $recent_registrations = InternshipRegistration::whereIn('student_profile_id', $studentProfileIds)
                ->with(['user', 'internship', 'studentProfile'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        }

        return view('admin.home', compact('stats', 'recent_registrations', 'payment_summary'));
    }
}
