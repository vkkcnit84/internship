@extends('layouts.admin')
@section('styles')
<style>
    .welcome-letter { max-width: 800px; margin: 0 auto; padding: 2.5rem 3rem; line-height: 1.8; font-size: 15px; }
    .welcome-letter .letter-head { display: flex; justify-content: space-between; margin-bottom: 2rem; }
    .welcome-letter h4 { text-align: center; text-decoration: underline; margin: 1.5rem 0; }
    @media print {
        .app-header, .sidebar, .no-print { display: none !important; }
        body.app { padding-top: 0 !important; }
        .main { margin-left: 0 !important; }
        .card { box-shadow: none !important; }
    }
</style>
@endsection
@section('content')
@php
    $paid = $registrations->filter(function ($r) { return $r->isPaid(); });
@endphp
@if($paid->isEmpty())
    <div class="alert alert-info">
        Your welcome letter will be available once your internship payment is confirmed.
    </div>
@endif
@foreach($paid as $registration)
    <div class="card">
        <div class="card-body">
            <div class="welcome-letter">
                <div class="letter-head">
                    <div>
                        @if($profile && $profile->college && $profile->college->logo_url)
                            <img src="{{ $profile->college->logo_url }}" alt="{{ $profile->college->name }}" style="max-height: 70px; max-width: 200px; margin-bottom: 6px;"><br>
                        @endif
                        <strong>{{ trans('panel.site_title') }}</strong>
                    </div>
                    <div class="text-right">
                        Ref: WL-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}<br>
                        Date: {{ ($registration->paid_at ?: now())->format('d M Y') }}
                    </div>
                </div>

                <p>
                    To,<br>
                    <strong>{{ $user->name }}</strong><br>
                    @if($profile)
                        {{ $profile->college->name ?? '' }}{{ $profile->university ? ', ' . $profile->university->name : '' }}<br>
                    @endif
                    {{ $user->email }}
                </p>

                <h4>Welcome to the Internship Programme</h4>

                <p>Dear {{ $user->name }},</p>

                <p>
                    We are pleased to confirm your registration for the
                    <strong>{{ $registration->internship->name ?? 'internship' }}</strong> programme
                    @if($profile && $profile->stream)
                        under the <strong>{{ $profile->stream->name }}</strong> stream
                        {{ $profile->subject ? '(' . $profile->subject->name . ')' : '' }}
                    @endif
                    @if($registration->internship && $registration->internship->duration_weeks)
                        for a duration of {{ $registration->internship->duration_weeks }} weeks
                    @endif
                    <span style="margin-left:-0.3em">.</span> Your payment of &#8377;{{ number_format($registration->total_amount, 2) }} has been received and confirmed.
                </p>

                @if($profile)
                    <p>
                        Registration number: <strong>{{ $profile->registration_number }}</strong> &middot;
                        Roll number: <strong>{{ $profile->roll_number }}</strong> &middot;
                        Session: <strong>{{ $profile->academic_session }}</strong>
                    </p>
                @endif

                <p>
                    On successful completion of the internship you will receive a certificate of internship.
                    We wish you a rewarding learning experience.
                </p>

                <p class="mt-4">Warm regards,<br><strong>{{ trans('panel.site_title') }}</strong></p>
            </div>
        </div>
    </div>
@endforeach

@if($paid->isNotEmpty())
    <button type="button" class="btn btn-primary no-print" onclick="window.print()"><i class="fas fa-print"></i> Print / Save as PDF</button>
@endif
@endsection
