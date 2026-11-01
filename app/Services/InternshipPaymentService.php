<?php

namespace App\Services;

use App\InternshipRegistration;
use App\Payment;
use App\Services\Razorpay\RazorpayClient;
use Illuminate\Support\Facades\DB;

class InternshipPaymentService
{
    protected $gateway;

    public function __construct(RazorpayClient $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Returns the open payment (gateway order) for a registration, creating one
     * if there is none for the current amount. Reusing the order keeps repeated
     * clicks on "Pay" from piling up orders.
     */
    public function startPayment(InternshipRegistration $registration): Payment
    {
        $amount = $registration->getAmountPaise();

        $open = $registration->payments()
            ->where('status', Payment::STATUS_CREATED)
            ->where('amount_paise', $amount)
            ->latest('id')
            ->first();

        if ($open) {
            return $open;
        }

        $attempt = $registration->payments()->count() + 1;

        $order = $this->gateway->createOrder($amount, "INT-{$registration->id}-{$attempt}", [
            'registration_id' => (string) $registration->id,
        ]);

        return $registration->payments()->create([
            'gateway'          => 'razorpay',
            'gateway_order_id' => $order['id'],
            'amount_paise'     => $amount,
            'currency'         => config('razorpay.currency'),
            'status'           => Payment::STATUS_CREATED,
        ]);
    }

    /**
     * Idempotent: the checkout callback and the webhook can both arrive for the
     * same payment, and only the first one changes anything.
     */
    public function markPaid(Payment $payment, string $gatewayPaymentId, array $payload = []): Payment
    {
        return DB::transaction(function () use ($payment, $gatewayPaymentId, $payload) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === Payment::STATUS_PAID) {
                return $payment;
            }

            $payment->update([
                'status'             => Payment::STATUS_PAID,
                'gateway_payment_id' => $gatewayPaymentId,
                'paid_at'            => now(),
                'gateway_response'   => $payload ? json_encode($payload) : null,
            ]);

            $registration = InternshipRegistration::lockForUpdate()->findOrFail($payment->internship_registration_id);

            if (!$registration->isPaid()) {
                $registration->update([
                    'status'  => InternshipRegistration::STATUS_PAID,
                    'paid_at' => now(),
                ]);
            }

            return $payment;
        });
    }

    public function markFailed(Payment $payment, array $payload = []): void
    {
        // A paid payment is never downgraded by a late "failed" event.
        if ($payment->status === Payment::STATUS_CREATED) {
            $payment->update([
                'status'           => Payment::STATUS_FAILED,
                'gateway_response' => $payload ? json_encode($payload) : null,
            ]);
        }
    }
}
