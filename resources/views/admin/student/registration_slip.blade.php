@extends('layouts.admin')
@section('styles')
<style>
    .slip { max-width: 800px; margin: 0 auto 24px; background: #fff; padding: 36px 44px; border: 1px solid #e2e8f0; border-radius: 8px; color: #1e293b; }
    .slip .brand { text-align: center; background: #002338; border-radius: 8px; padding: 10px; margin-bottom: 18px; }
    .slip .brand img { height: 56px; }
    .slip h4 { text-align: center; font-weight: 700; margin: 0 0 4px; }
    .slip .note { text-align: center; color: #64748b; font-size: 13px; margin-bottom: 22px; }
    .slip h6 { font-weight: 700; font-size: 15px; border-bottom: 2px solid #22c55e; padding-bottom: 6px; margin: 22px 0 8px; }
    .slip table { width: 100%; }
    .slip td { padding: 6px 4px; vertical-align: top; font-size: 14px; }
    .slip td:first-child { width: 42%; font-weight: 600; color: #475569; }
    .slip .foot { text-align: center; color: #64748b; font-size: 12px; margin-top: 26px; }
    @media print {
        .app-header, .sidebar, .no-print { display: none !important; }
        body.app { padding-top: 0 !important; }
        .main { margin-left: 0 !important; padding: 0 !important; }
        .slip { border: 0; margin: 0; page-break-after: always; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@endsection
@section('content')
@if($registrations->isEmpty())
    <div class="alert alert-info">You have no registration yet.</div>
@endif
@foreach($registrations as $registration)
    @php($payment = $registration->payments->where('status', 'paid')->sortByDesc('paid_at')->first())
    <div class="slip">
        <div class="brand"><img src="{{ asset('img/logo.png') }}" alt="{{ trans('panel.site_title') }}"></div>
        <h4>Student Registration Slip</h4>
        <div class="note">This slip confirms your registration for the internship.</div>

        <h6>Payment Details</h6>
        <table>
            <tr><td>Receipt Number</td><td>IK-R-{{ str_pad($registration->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
            @if($payment)
                <tr><td>Order ID</td><td>{{ $payment->gateway_order_id }}</td></tr>
                <tr><td>Payment ID</td><td>{{ $payment->gateway_payment_id }}</td></tr>
            @endif
            <tr><td>Payment Method</td><td>{{ \App\InternshipRegistration::PAYMENT_METHODS[$registration->payment_method] ?? $registration->payment_method }}</td></tr>
            <tr><td>Payment Status</td><td>{{ \App\InternshipRegistration::STATUS_SELECT[$registration->status] ?? $registration->status }}</td></tr>
            <tr><td>Payment Date</td><td>{{ $registration->paid_at ? $registration->paid_at->format('j M Y, g:i a') : '-' }}</td></tr>
            <tr><td>Amount</td><td>Rs. {{ number_format($registration->total_amount, 2) }}</td></tr>
        </table>

        <h6>Student Registration Details</h6>
        <table>
            <tr><td>Registration No.</td><td>IK-{{ $registration->created_at->format('Y') }}-{{ str_pad($registration->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
            <tr><td>University Registration No.</td><td>{{ $profile->registration_number ?? '' }}</td></tr>
            <tr><td>University Roll No.</td><td>{{ $profile->roll_number ?? '' }}</td></tr>
            <tr><td>Student Name</td><td>{{ $user->name }}</td></tr>
            <tr><td>Father / Guardian Name</td><td>{{ $profile->guardian_name ?? '' }}</td></tr>
            <tr><td>Email</td><td>{{ $user->email }}</td></tr>
            <tr><td>Mobile</td><td>{{ $profile->mobile ?? '' }}</td></tr>
            <tr><td>Course (Stream)</td><td>{{ $profile->stream->name ?? '' }}</td></tr>
            <tr><td>Major Subject</td><td>{{ $profile->subject->name ?? '' }}</td></tr>
            <tr><td>Session</td><td>{{ $profile->academic_session ?? '' }}</td></tr>
            <tr><td>Class / Semester</td><td>{{ $profile->class_semester ?? '' }}</td></tr>
            <tr><td>University</td><td>{{ $profile->university->name ?? '' }}</td></tr>
            <tr><td>College</td><td>{{ $profile->college->name ?? '' }}</td></tr>
            <tr><td>Internship</td><td>{{ $registration->internship->name ?? '' }}</td></tr>
            <tr><td>Registration Status</td><td>{{ $registration->isPaid() ? 'Active' : 'Pending' }}</td></tr>
        </table>

        <div class="foot">
            This is a computer-generated slip and does not require a physical signature.<br>
            Generated on {{ now()->format('j M Y, g:i a') }}
        </div>
    </div>
@endforeach

@if($registrations->isNotEmpty())
    <button type="button" class="btn btn-primary no-print" onclick="window.print()"><i class="fas fa-download"></i> Download / Print Slip</button>
@endif
@endsection
