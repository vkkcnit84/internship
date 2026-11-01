<?php

namespace App\Services\Razorpay;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

/**
 * Thin wrapper over the Razorpay REST API (Guzzle is already a dependency,
 * so the official SDK is not needed). Only what the internship checkout uses.
 */
class RazorpayClient
{
    protected $http;

    public function isConfigured(): bool
    {
        return config('razorpay.key_id') && config('razorpay.key_secret');
    }

    public function keyId(): ?string
    {
        return config('razorpay.key_id');
    }

    /**
     * 'live' for rzp_live_* keys, otherwise 'test'.
     */
    public function mode(): string
    {
        return strpos((string) config('razorpay.key_id'), 'rzp_live_') === 0 ? 'live' : 'test';
    }

    /**
     * @return array Razorpay order payload (contains 'id').
     * @throws RuntimeException when the gateway is unconfigured or rejects the request.
     */
    public function createOrder(int $amountPaise, string $receipt, array $notes = []): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Razorpay is not configured. Set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in .env.');
        }

        try {
            $response = $this->http()->post('orders', [
                'json' => [
                    'amount'   => $amountPaise,
                    'currency' => config('razorpay.currency'),
                    'receipt'  => $receipt,
                    'notes'    => $notes,
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Could not reach the payment gateway: ' . $e->getMessage(), 0, $e);
        }

        $order = json_decode((string) $response->getBody(), true);

        if (!is_array($order) || empty($order['id'])) {
            throw new RuntimeException('Unexpected response from the payment gateway.');
        }

        return $order;
    }

    /**
     * Checkout callback signature: HMAC-SHA256("{order_id}|{payment_id}", key_secret).
     */
    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        $secret = (string) config('razorpay.key_secret');

        if ($secret === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $orderId . '|' . $paymentId, $secret), $signature);
    }

    /**
     * Webhook signature: HMAC-SHA256(raw request body, webhook_secret).
     */
    public function verifyWebhookSignature(string $body, string $signature): bool
    {
        $secret = (string) config('razorpay.webhook_secret');

        if ($secret === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $body, $secret), $signature);
    }

    protected function http(): Client
    {
        if (!$this->http) {
            $this->http = new Client([
                'base_uri' => config('razorpay.base_url'),
                'auth'     => [config('razorpay.key_id'), config('razorpay.key_secret')],
                'timeout'  => 15,
            ]);
        }

        return $this->http;
    }
}
