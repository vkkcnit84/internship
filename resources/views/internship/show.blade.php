@extends('layouts.main')

@section('content')
@php($profile = $registration->user->studentProfile)
<div class="whole-wrap">
    <div class="container box_1170">
        <div class="section-top-border">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    @if($registration->isPaid())
                        <div class="alert alert-success" role="alert">
                            <strong>Payment received.</strong> Your internship registration is confirmed.
                        </div>
                    @elseif($registration->payment_method === 'offline')
                        @if($registration->approval_status === 'approved')
                            <div class="alert alert-success" role="alert">
                                <strong>Payment approved!</strong> Your internship registration is confirmed.
                            </div>
                        @elseif($registration->approval_status === 'rejected')
                            <div class="alert alert-danger" role="alert">
                                <strong>Payment rejected.</strong> Please contact admin for details.
                            </div>
                        @else
                            <div class="alert alert-warning" role="alert">
                                <strong>Offline payment pending.</strong> Your registration is submitted. Admin will review and approve your payment shortly.
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info" role="alert">
                            Your account is created. Complete the payment below to activate your internship.
                        </div>
                    @endif

                    <div id="payError" class="alert alert-danger" role="alert" style="display: none;"></div>

                    <h3 class="mb-30">Registration summary</h3>
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th>Student</th><td>{{ $registration->user->name }} ({{ $registration->user->email }})</td></tr>
                            <tr><th>Programme</th><td>{{ $registration->internship->name }}</td></tr>
                            @if($profile)
                                <tr><th>University</th><td>{{ $profile->university->name ?? '' }}</td></tr>
                                <tr><th>College</th><td>{{ $profile->college->name ?? '' }}</td></tr>
                                <tr><th>Stream / Subject</th><td>{{ $profile->stream->name ?? '' }} / {{ $profile->subject->name ?? '' }}</td></tr>
                            @endif
                            <tr><th>Fee</th><td>&#8377;{{ number_format($registration->price, 2) }}</td></tr>
                            @if($registration->tax_amount > 0)
                                <tr><th>Tax ({{ rtrim(rtrim(number_format($registration->tax_percent, 2, '.', ''), '0'), '.') }}%)</th><td>&#8377;{{ number_format($registration->tax_amount, 2) }}</td></tr>
                            @endif
                            <tr><th>Total</th><td><strong>&#8377;{{ number_format($registration->total_amount, 2) }}</strong></td></tr>
                            <tr><th>Payment Method</th><td>{{ \App\InternshipRegistration::PAYMENT_METHODS[$registration->payment_method] ?? $registration->payment_method }}</td></tr>
                            <tr><th>Payment Status</th><td>{{ \App\InternshipRegistration::STATUS_SELECT[$registration->status] ?? $registration->status }}@if($registration->paid_at) &middot; {{ $registration->paid_at->format('d M Y, H:i') }}@endif</td></tr>
                            @if($registration->payment_method === 'offline')
                                <tr><th>Approval Status</th><td>
                                    @if($registration->approval_status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($registration->approval_status === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td></tr>
                            @endif
                        </tbody>
                    </table>

                    @unless($registration->isPaid())
                        @if($registration->payment_method === 'online')
                            <button type="button" id="payButton" class="genric-btn primary">Pay &#8377;{{ number_format($registration->total_amount, 2) }}</button>
                            @if($gateway->mode() === 'test')
                                <p class="mt-2 text-muted"><small>Test mode: no real money is charged.</small></p>
                            @endif
                        @elseif($registration->approval_status === 'pending')
                            <p class="alert alert-info mt-3">
                                <strong>Note:</strong> Your offline payment request is being reviewed. No action needed from your end. You'll be notified once it's approved.
                            </p>
                        @endif
                    @endunless
                </div>
            </div>
        </div>
    </div>
</div>

@unless($registration->isPaid())
    @if($registration->payment_method === 'online')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    var button = document.getElementById('payButton');
    if (!button) return;

    var errorBox = document.getElementById('payError');
    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    function fail(message) {
        errorBox.textContent = message;
        errorBox.style.display = 'block';
        button.disabled = false;
    }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(body || {})
        }).then(function (r) {
            return r.json().then(function (json) {
                if (!r.ok) { throw new Error(json.message || 'Something went wrong.'); }
                return json;
            });
        });
    }

    button.addEventListener('click', function () {
        errorBox.style.display = 'none';
        button.disabled = true;

        post("{{ route('internship.payment.order', $registration) }}").then(function (order) {
            var checkout = new Razorpay({
                key: order.key,
                order_id: order.order_id,
                amount: order.amount,
                currency: order.currency,
                name: order.name,
                description: order.description,
                prefill: order.prefill,
                handler: function (response) {
                    post("{{ route('internship.payment.verify', $registration) }}", response)
                        .then(function (result) { window.location = result.redirect; })
                        .catch(function (e) { fail(e.message + ' If money was deducted it will be confirmed automatically; check this page again shortly.'); });
                },
                modal: { ondismiss: function () { button.disabled = false; } }
            });
            checkout.on('payment.failed', function () { fail('Payment failed. You can try again.'); });
            checkout.open();
        }).catch(function (e) { fail(e.message); });
    });
})();
</script>
    @endif
@endunless
@endsection
