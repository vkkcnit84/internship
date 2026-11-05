<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class StudentPortalController extends Controller
{
    public function profile()
    {
        $user = $this->student();
        $user->load('studentProfile.university', 'studentProfile.college', 'studentProfile.stream', 'studentProfile.subject');

        return view('admin.student.profile', [
            'user'          => $user,
            'profile'       => $user->studentProfile,
            'registrations' => $user->internshipRegistrations()->with('internship')->latest()->get(),
        ]);
    }

    public function welcomeLetter()
    {
        $user = $this->student();
        $user->load('studentProfile.university', 'studentProfile.college', 'studentProfile.stream', 'studentProfile.subject');

        return view('admin.student.welcome_letter', [
            'user'          => $user,
            'profile'       => $user->studentProfile,
            'registrations' => $user->internshipRegistrations()->with('internship')->latest()->get(),
        ]);
    }

    public function registrationSlip()
    {
        $user = $this->student();
        $user->load('studentProfile.university', 'studentProfile.college', 'studentProfile.stream', 'studentProfile.subject');

        return view('admin.student.registration_slip', [
            'user'          => $user,
            'profile'       => $user->studentProfile,
            'registrations' => $user->internshipRegistrations()->with('internship', 'payments')->latest()->get(),
        ]);
    }

    public function acceptanceLetter()
    {
        $user = $this->student();
        $user->load('studentProfile.university', 'studentProfile.college');

        return view('admin.student.acceptance_letter', [
            'user'          => $user,
            'profile'       => $user->studentProfile,
            'registrations' => $user->internshipRegistrations()->with('internship')->latest()->get(),
        ]);
    }

    private function student()
    {
        $user = auth()->user();
        abort_unless($user->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $user;
    }
}
