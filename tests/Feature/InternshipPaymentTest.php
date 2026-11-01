<?php

namespace Tests\Feature;

use App\InternshipRegistration;
use App\Payment;
use App\Services\Razorpay\RazorpayClient;
use App\User;

class InternshipPaymentTest extends InternshipTestCase
{
    protected function pendingRegistration(array $internship = []): InternshipRegistration
    {
        $chain = $this->chain();
        $this->post('/internship/register', $this->registrationPayload($chain, $this->internship($internship, $chain)));

        return InternshipRegistration::firstOrFail();
    }

    protected function sign(string $orderId, string $paymentId): string
    {
        return hash_hmac('sha256', $orderId . '|' . $paymentId, 'test_secret');
    }

    public function test_status_page_shows_the_summary_and_pay_button()
    {
        $registration = $this->pendingRegistration(['price' => 1000, 'tax_percent' => 18]);
        // Set payment_method to online so Test mode message appears
        $registration->update(['payment_method' => 'online']);

        $this->get(route('internship.registration.show', $registration))
            ->assertOk()->assertSee('1,180.00')->assertSee('Pay')->assertSee('Test mode');
    }

    public function test_order_uses_the_server_side_amount_and_is_reused()
    {
        $registration = $this->pendingRegistration(['price' => 1000, 'tax_percent' => 18]);
        $url = route('internship.payment.order', $registration);

        $first = $this->postJson($url)->assertOk()->json();
        $this->assertSame(118000, $first['amount']);
        $this->assertSame('INR', $first['currency']);
        $this->assertSame('rzp_test_abc', $first['key']);

        $second = $this->postJson($url)->assertOk()->json();
        $this->assertSame($first['order_id'], $second['order_id']);
        $this->assertSame(1, Payment::count());
    }

    public function test_valid_signature_marks_the_registration_paid()
    {
        $registration = $this->pendingRegistration();
        $order = $this->postJson(route('internship.payment.order', $registration))->json();

        $this->postJson(route('internship.payment.verify', $registration), [
            'razorpay_order_id'   => $order['order_id'],
            'razorpay_payment_id' => 'pay_123',
            'razorpay_signature'  => $this->sign($order['order_id'], 'pay_123'),
        ])->assertOk()->assertJson(['redirect' => route('internship.registration.show', $registration)]);

        $this->assertTrue($registration->fresh()->isPaid());
        $this->assertDatabaseHas('payments', ['gateway_order_id' => $order['order_id'], 'status' => 'paid', 'gateway_payment_id' => 'pay_123']);

        $this->get(route('internship.registration.show', $registration))->assertSee('Payment received');
        $this->postJson(route('internship.payment.order', $registration))->assertStatus(409);
    }

    public function test_forged_signature_does_not_mark_anything_paid()
    {
        $registration = $this->pendingRegistration();
        $order = $this->postJson(route('internship.payment.order', $registration))->json();

        $this->postJson(route('internship.payment.verify', $registration), [
            'razorpay_order_id'   => $order['order_id'],
            'razorpay_payment_id' => 'pay_123',
            'razorpay_signature'  => 'forged',
        ])->assertStatus(422);

        $this->assertFalse($registration->fresh()->isPaid());
        $this->assertDatabaseHas('payments', ['gateway_order_id' => $order['order_id'], 'status' => 'created']);
    }

    public function test_a_student_cannot_touch_someone_elses_registration()
    {
        $registration = $this->pendingRegistration();
        $this->post('/logout');

        $other = User::create(['name' => 'Other', 'email' => 'other@example.com', 'password' => 'password']);

        $this->actingAs($other)->get(route('internship.registration.show', $registration))->assertForbidden();
        $this->actingAs($other)->postJson(route('internship.payment.order', $registration))->assertForbidden();
        $this->actingAs($other)->postJson(route('internship.payment.verify', $registration), [
            'razorpay_order_id' => 'x', 'razorpay_payment_id' => 'y', 'razorpay_signature' => 'z',
        ])->assertForbidden();

        auth()->logout();
        $this->get(route('internship.registration.show', $registration))->assertRedirect('/login');
    }

    public function test_webhook_confirms_payment_and_is_idempotent()
    {
        $registration = $this->pendingRegistration(['price' => 500]);
        $order = $this->postJson(route('internship.payment.order', $registration))->json();
        auth()->logout();

        $body = json_encode([
            'event'   => 'payment.captured',
            'payload' => ['payment' => ['entity' => ['id' => 'pay_w1', 'order_id' => $order['order_id'], 'amount' => 50000]]],
        ]);
        $signature = hash_hmac('sha256', $body, 'webhook_secret');

        for ($i = 0; $i < 2; $i++) {
            $this->call('POST', '/internship/razorpay/webhook', [], [], [], [
                'HTTP_X_RAZORPAY_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json',
            ], $body)->assertOk()->assertJson(['status' => 'ok']);
        }

        $this->assertTrue($registration->fresh()->isPaid());
        $this->assertSame(1, Payment::where('status', 'paid')->count());
    }

    public function test_webhook_rejects_bad_signatures_and_amount_mismatches()
    {
        $registration = $this->pendingRegistration(['price' => 500]);
        $order = $this->postJson(route('internship.payment.order', $registration))->json();
        auth()->logout();

        $body = json_encode([
            'event'   => 'payment.captured',
            'payload' => ['payment' => ['entity' => ['id' => 'pay_w1', 'order_id' => $order['order_id'], 'amount' => 100]]],
        ]);

        $this->call('POST', '/internship/razorpay/webhook', [], [], [], [
            'HTTP_X_RAZORPAY_SIGNATURE' => 'forged', 'CONTENT_TYPE' => 'application/json',
        ], $body)->assertStatus(400);

        $this->call('POST', '/internship/razorpay/webhook', [], [], [], [
            'HTTP_X_RAZORPAY_SIGNATURE' => hash_hmac('sha256', $body, 'webhook_secret'), 'CONTENT_TYPE' => 'application/json',
        ], $body)->assertJson(['status' => 'amount_mismatch']);

        $this->assertFalse($registration->fresh()->isPaid());
    }

    public function test_a_late_failed_event_does_not_undo_a_paid_payment()
    {
        $registration = $this->pendingRegistration();
        $order = $this->postJson(route('internship.payment.order', $registration))->json();
        $this->postJson(route('internship.payment.verify', $registration), [
            'razorpay_order_id' => $order['order_id'], 'razorpay_payment_id' => 'pay_1',
            'razorpay_signature' => $this->sign($order['order_id'], 'pay_1'),
        ])->assertOk();
        auth()->logout();

        $body = json_encode(['event' => 'payment.failed', 'payload' => ['payment' => ['entity' => ['id' => 'pay_0', 'order_id' => $order['order_id']]]]]);
        $this->call('POST', '/internship/razorpay/webhook', [], [], [], [
            'HTTP_X_RAZORPAY_SIGNATURE' => hash_hmac('sha256', $body, 'webhook_secret'), 'CONTENT_TYPE' => 'application/json',
        ], $body)->assertOk();

        $this->assertDatabaseHas('payments', ['gateway_order_id' => $order['order_id'], 'status' => 'paid']);
    }

    public function test_gateway_errors_are_reported_without_leaking_details()
    {
        $registration = $this->pendingRegistration();

        $this->app->instance(RazorpayClient::class, new class extends RazorpayClient {
            public function createOrder(int $amountPaise, string $receipt, array $notes = []): array
            {
                throw new \RuntimeException('secret internal detail');
            }
        });

        $this->postJson(route('internship.payment.order', $registration))
            ->assertStatus(502)->assertDontSee('secret internal detail');
    }

    public function test_mode_follows_the_key_prefix()
    {
        $client = new RazorpayClient;
        $this->assertSame('test', $client->mode());

        config(['razorpay.key_id' => 'rzp_live_xyz']);
        $this->assertSame('live', $client->mode());
    }
}
