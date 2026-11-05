@extends('layouts.admin')
@section('styles')
<style>
    .letter-page {
        position: relative; width: 210mm; min-height: 297mm; margin: 0 auto 20px; box-sizing: border-box;
        background: #fff url('{{ asset('img/letterhead.png') }}') no-repeat center top / 100% 100%;
        padding: 72mm 24mm 40mm; font-family: 'Times New Roman', Times, serif; font-size: 16px; line-height: 1.5; color: #000;
        box-shadow: 0 2px 12px rgba(0,0,0,.2);
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .letter-page h3 { text-align: center; font-weight: 400; font-size: 22px; margin: 0; }
    .letter-page .sub { text-align: center; margin-bottom: 18px; }
    .letter-page .row-lr { display: flex; justify-content: space-between; margin-bottom: 14px; }
    .letter-page p { text-align: justify; margin: 0 0 14px; }
    .letter-page .sign { margin-top: 36px; }
    @page { size: A4; margin: 0; }
    @media print {
        .app-header, .sidebar, .no-print { display: none !important; }
        body.app { padding-top: 0 !important; }
        .main { margin-left: 0 !important; padding: 0 !important; }
        .container-fluid { padding: 0 !important; }
        .letter-page { margin: 0; box-shadow: none; page-break-after: always; }
    }
</style>
@endsection
@section('content')
@php
    $paid = $registrations->filter(function ($r) { return $r->isPaid(); });
@endphp
@if($paid->isEmpty())
    <div class="alert alert-info">
        The internship acceptance letter is available once the internship payment is confirmed.
    </div>
@endif
@foreach($paid as $registration)
    <div class="letter-page">
        <h3>INTERNSHIP ACCEPTANCE LETTER</h3>
        <div class="sub">(on the letter head of the organization)</div>

        <div class="row-lr">
            <div>Letter No. IK/AL/{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div>Date: {{ ($registration->paid_at ?: now())->format('d/m/Y') }}</div>
        </div>

        <p>To</p>
        <p><strong>{{ $user->name }}</strong> (Student Name)</p>
        <p><strong>{{ $profile->college->name ?? '' }}</strong> (Institution)</p>

        <p>Dear Candidate,</p>

        <p>
            We are pleased to accept your application and offer you internship at our organization. Our organizations
            satisfy all the requirements as provided in the Internship Guidelines of
            <strong>{{ trim(preg_replace('/\s+university$/i', '', $profile->university->name ?? '')) ?: '.................' }}</strong>
            University for Undergraduate Programmes.
        </p>

        <p>We appreciate your interest in our organization.</p>

        <p>Thank you.</p>

        <div class="sign">
            Supervisor/Head IPO<br>
            (Signature and seal)
        </div>
    </div>
@endforeach

@if($paid->isNotEmpty())
    <button type="button" class="btn btn-primary no-print" onclick="window.print()"><i class="fas fa-print"></i> Print / Save as PDF</button>
@endif
@endsection
