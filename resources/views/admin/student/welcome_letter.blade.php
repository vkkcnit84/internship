@extends('layouts.admin')
@section('styles')
<style>
    .consent-letter { max-width: 800px; margin: 0 auto; padding: 2.5rem 3rem; font-family: 'Times New Roman', Times, serif; font-size: 17px; line-height: 1.5; color: #000; }
    .consent-letter h3 { text-align: center; font-weight: 700; margin: 0 0 1.5rem; }
    .consent-letter h5 { text-align: center; text-decoration: underline; font-weight: 400; margin: 0 0 1.5rem; font-size: 18px; }
    .consent-letter p { text-align: justify; }
    .consent-letter .logo { text-align: center; margin-bottom: 1rem; }
    .consent-letter table { width: 100%; border-collapse: collapse; margin: 0.5rem 0 3rem; }
    .consent-letter td { border: 1px solid #ccc; padding: 4px 14px; width: 50%; height: 34px; }
    .consent-letter .signature { text-align: center; margin-top: 6rem; }
    @media print {
        .app-header, .sidebar, .no-print { display: none !important; }
        body.app { padding-top: 0 !important; }
        .main { margin-left: 0 !important; }
        .card { box-shadow: none !important; border: 0 !important; }
    }
</style>
@endsection
@section('content')
@php
    $paid = $registrations->filter(function ($r) { return $r->isPaid(); });
@endphp
@if($paid->isEmpty())
    <div class="alert alert-info">
        The consent letter is available once the internship payment is confirmed.
    </div>
@endif
@foreach($paid as $registration)
    <div class="card">
        <div class="card-body">
            <div class="consent-letter">
                @if($profile && $profile->college && $profile->college->logo_url)
                    <div class="logo"><img src="{{ $profile->college->logo_url }}" alt="{{ $profile->college->name }}" style="max-height: 70px; max-width: 200px;"></div>
                @endif

                <h3>CONSENT LETTER</h3>
                <h5>TO WHOME IT MAY CONCERN</h5>

                <p>
                    As part of the curriculum of
                    <strong>{{ $registration->internship->name ?? '---------------------------' }}</strong>
                    (name of programme/course), the students are required to undertake internship of minimum
                    {{ $registration->internship && $registration->internship->duration_weeks ? $registration->internship->duration_weeks . ' weeks' : '................' }}
                    Credit (&hellip;&hellip;&hellip;&hellip; hours) in an organization. In this regard, I am introducing the
                    above-mentioned student with the following details for the purpose of the internship:
                </p>

                <table>
                    <tr><td>Name of the Student</td><td>{{ $user->name }}</td></tr>
                    <tr><td>Gender</td><td>{{ \App\StudentProfile::GENDER_SELECT[$profile->gender ?? ''] ?? '' }}</td></tr>
                    <tr><td>Father&rsquo;s Name</td><td>{{ $profile->guardian_name ?? '' }}</td></tr>
                    <tr><td>University Registration Number</td><td>{{ $profile->registration_number ?? '' }}</td></tr>
                    <tr><td>College Roll Number</td><td>{{ $profile->roll_number ?? '' }}</td></tr>
                    <tr><td>Class with Semester</td><td>{{ $profile->class_semester ?? '' }}</td></tr>
                    <tr><td>Mobile Number</td><td>{{ $profile->mobile ?? '' }}</td></tr>
                    <tr><td>Email Id.</td><td>{{ $user->email }}</td></tr>
                </table>

                <p>
                    The organization satisfying the requirement of the Internship as provided in the guidelines of the
                    University are requested to consider the candidature of the above-mentioned student for internship
                    in your organization.
                </p>

                <div class="signature">
                    Signature of the Head of the Department of College/Internship Nodal Officer/Principal with Seal
                </div>
            </div>
        </div>
    </div>
@endforeach

@if($paid->isNotEmpty())
    <button type="button" class="btn btn-primary no-print" onclick="window.print()"><i class="fas fa-print"></i> Print / Save as PDF</button>
@endif
@endsection
