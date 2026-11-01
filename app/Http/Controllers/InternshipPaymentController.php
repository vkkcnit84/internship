<?php

namespace App\Http\Controllers;

use App\InternshipRegistration;
use App\Payment;
use App\Services\InternshipPaymentService;
use App\Services\Razorpay\RazorpayClient;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class InternshipPaymentController extends Controller
{
    protected $gateway;
    protected $payments;

    public function __construct(RazorpayClient $gateway, InternshipPaymentService $payments)
    {
        $this->gateway  = $gateway;
        $this->payments = $payments;
    }

    public function show(InternshipRegistration $registration)
    {
        $this->authorizeOwner($registration);

        $registration->load('internship', 'user.studentProfile');

        return view('internship.show', [
            'breadcrumb'   => 'Internship Registration',
            'registration' => $registration,
            'gateway'      => $this->gateway,
        ]);
    }

    /**
     * Creates (or reuses) the Razorpay order and returns what Checkout needs.
     * The amount always comes from the server-side registration snapshot.
     */
    public function order(InternshipRegistration $registration)
    {
        $this->authorizeOwner($registration);

        if ($registration->isPaid()) {
            return response()->json(['message' => 'This registration is already paid.'], Response::HTTP_CONFLICT);
        }

        try {
            $payment = $this->payments->startPayment($registration);
        } catch (RuntimeException $e) {
            report($e);

            return response()->json(['message' => 'The payment gateway is unavailable right now. Please try again shortly.'], Response::HTTP_BAD_GATEWAY);
        }

        $user = $registration->user;

        return response()->json([
            'key'         => $this->gateway->keyId(),
            'order_id'    => $payment->gateway_order_id,
            'amount'      => $payment->amount_paise,
            'currency'    => $payment->currency,
            'name'        => config('razorpay.merchant_name'),
            'description' => $registration->internship->name,
            'prefill'     => [
                'name'    => $user->name,
                'email'   => $user->email,
                'contact' => optional($user->studentProfile)->mobile,
            ],
        ]);
    }

    /**
     * Razorpay Checkout success callback. Never trust the browser: the HMAC
     * signature is what proves the payment.
     */
    public function verify(Request $request, InternshipRegistration $registration)
    {
        $this->authorizeOwner($registration);

        $data = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $payment = $registration->payments()->where('gateway_order_id', $data['razorpay_order_id'])->first();

        if (!$payment || !$this->gateway->verifyPaymentSignature($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature'])) {
            return response()->json(['message' => 'Payment verification failed.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->payments->markPaid($payment, $data['razorpay_payment_id'], $data);

        return response()->json(['redirect' => route('internship.registration.show', $registration)]);
    }

    /**
     * Server-to-server confirmation, so a payment is still recorded if the
     * student closes the tab before the checkout callback fires.
     */
    public function webhook(Request $request)
    {
        $body = $request->getContent();

        if (!$this->gateway->verifyWebhookSignature($body, (string) $request->header('X-Razorpay-Signature'))) {
            return response()->json(['message' => 'Invalid signature.'], Response::HTTP_BAD_REQUEST);
        }

        $event     = $request->input('event');
        $entity    = $request->input('payload.payment.entity', []);
        $orderId   = $entity['order_id'] ?? $request->input('payload.order.entity.id');
        $paymentId = $entity['id'] ?? null;

        $payment = $orderId ? Payment::where('gateway_order_id', $orderId)->first() : null;

        // Unknown orders are acknowledged so the gateway does not keep retrying.
        if (!$payment) {
            return response()->json(['status' => 'ignored']);
        }

        if (in_array($event, ['payment.captured', 'order.paid'], true) && $paymentId) {
            if ((int) ($entity['amount'] ?? $payment->amount_paise) !== (int) $payment->amount_paise) {
                report(new RuntimeException("Razorpay webhook amount mismatch for order {$orderId}."));

                return response()->json(['status' => 'amount_mismatch']);
            }

            $this->payments->markPaid($payment, $paymentId, $request->all());
        } elseif ($event === 'payment.failed') {
            $this->payments->markFailed($payment, $request->all());
        }

        return response()->json(['status' => 'ok']);
    }

    protected function authorizeOwner(InternshipRegistration $registration): void
    {
        abort_if((int) $registration->user_id !== (int) auth()->id(), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }
}
