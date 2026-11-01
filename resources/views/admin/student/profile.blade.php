@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Personal details</div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr><th style="width: 30%">Name</th><td>{{ $user->name }}</td></tr>
                <tr><th>Email</th><td>{{ $user->email }}</td></tr>
                @if($profile)
                    <tr><th>Mobile</th><td>{{ $profile->mobile }}</td></tr>
                    <tr><th>WhatsApp</th><td>{{ $profile->whatsapp }}</td></tr>
                    <tr><th>Guardian name</th><td>{{ $profile->guardian_name }}</td></tr>
                    <tr><th>Gender</th><td>{{ \App\StudentProfile::GENDER_SELECT[$profile->gender] ?? $profile->gender }}</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@if($profile)
<div class="card">
    <div class="card-header">Academic details</div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr><th style="width: 30%">University</th><td>{{ $profile->university->name ?? '' }}</td></tr>
                <tr><th>College</th><td>{{ $profile->college->name ?? '' }}</td></tr>
                <tr><th>Stream</th><td>{{ $profile->stream->name ?? '' }}</td></tr>
                <tr><th>Subject</th><td>{{ $profile->subject->name ?? '' }}</td></tr>
                <tr><th>Class / Semester</th><td>{{ $profile->class_semester }}</td></tr>
                <tr><th>Year of study</th><td>{{ $profile->year_of_study }}</td></tr>
                <tr><th>Registration number</th><td>{{ $profile->registration_number }}</td></tr>
                <tr><th>Roll number</th><td>{{ $profile->roll_number }}</td></tr>
                <tr><th>Academic session</th><td>{{ $profile->academic_session }}</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">My internships</div>
    <div class="card-body">
        @forelse($registrations as $registration)
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr><th style="width: 30%">Programme</th><td>{{ $registration->internship->name ?? '' }}</td></tr>
                    <tr><th>Fee</th><td>&#8377;{{ number_format($registration->total_amount, 2) }}</td></tr>
                    <tr><th>Payment method</th><td>{{ \App\InternshipRegistration::PAYMENT_METHODS[$registration->payment_method] ?? $registration->payment_method }}</td></tr>
                    <tr><th>Payment status</th><td>{{ \App\InternshipRegistration::STATUS_SELECT[$registration->status] ?? $registration->status }}@if($registration->paid_at) &middot; {{ $registration->paid_at->format('d M Y, H:i') }}@endif</td></tr>
                    @if($registration->payment_method === 'offline')
                        <tr><th>Approval status</th><td>{{ ucfirst($registration->approval_status) }}</td></tr>
                    @endif
                    @if($registration->certificate_number)
                        <tr><th>Certificate number</th><td>{{ $registration->certificate_number }}</td></tr>
                    @endif
                </tbody>
            </table>
            <a class="btn btn-primary" href="{{ route('internship.registration.show', $registration) }}">View registration</a>
        @empty
            <p class="mb-0">No internship registrations yet.</p>
        @endforelse
    </div>
</div>
@endsection
