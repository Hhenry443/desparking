<?php

/**
 * Minimal PayPal REST API (v1/v2) client — cURL only, no composer dependency.
 * Mirrors the handful of calls the Stripe SDK used to provide: create/capture
 * orders, refunds, subscription plans/lifecycle, and webhook verification.
 */
class PayPalClient
{
    public static function uuidv4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private static function getAccessToken(): string
    {
        $response = self::rawRequest('POST', '/v1/oauth2/token', 'grant_type=client_credentials', [
            'Content-Type: application/x-www-form-urlencoded',
        ], PAYPAL_CLIENT_ID . ':' . PAYPAL_SECRET);

        if (!isset($response['access_token'])) {
            throw new Exception('PayPal auth failed: ' . json_encode($response));
        }

        return $response['access_token'];
    }

    private static function request(string $method, string $path, ?array $body = null, array $extraHeaders = []): array
    {
        $token = self::getAccessToken();
        $headers = array_merge([
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ], $extraHeaders);

        return self::rawRequest($method, $path, $body !== null ? json_encode($body) : null, $headers);
    }

    private static function rawRequest(string $method, string $path, ?string $payload, array $headers, ?string $basicAuth = null): array
    {
        $ch = curl_init(PAYPAL_API_BASE . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $payload,
        ]);
        if ($basicAuth !== null) {
            curl_setopt($ch, CURLOPT_USERPWD, $basicAuth);
        }

        $raw     = curl_exec($ch);
        $errno   = curl_errno($ch);
        $error   = curl_error($ch);
        $status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            throw new Exception("PayPal request failed ({$path}): {$error}");
        }

        $decoded = $raw !== '' ? json_decode($raw, true) : [];

        if ($status >= 400) {
            throw new Exception("PayPal API error {$status} ({$path}): " . $raw);
        }

        return $decoded ?? [];
    }

    public static function createOrder(array $purchaseUnits): array
    {
        return self::request('POST', '/v2/checkout/orders', [
            'intent'         => 'CAPTURE',
            'purchase_units' => $purchaseUnits,
        ]);
    }

    public static function captureOrder(string $orderId): array
    {
        return self::request('POST', "/v2/checkout/orders/{$orderId}/capture");
    }

    public static function refundCapture(string $captureId, ?int $pence, string $currency): array
    {
        $body = [];
        if ($pence !== null) {
            $body['amount'] = [
                'value'         => Money::penceToDecimal($pence),
                'currency_code' => strtoupper($currency),
            ];
        }
        return self::request('POST', "/v2/payments/captures/{$captureId}/refund", $body);
    }

    /** Idempotent — reuses PAYPAL_PRODUCT_ID if already configured, otherwise creates one. */
    public static function getOrCreateProduct(): string
    {
        if (PAYPAL_PRODUCT_ID) {
            return PAYPAL_PRODUCT_ID;
        }

        $product = self::request('POST', '/v1/catalogs/products', [
            'name' => 'Monthly Parking Subscription',
            'type' => 'SERVICE',
        ]);

        error_log('PayPal: created catalog product ' . $product['id'] . ' — set PAYPAL_PRODUCT_ID env var to reuse it.');

        return $product['id'];
    }

    public static function createPlan(string $productId, int $priceCents, string $name, string $currency = 'GBP'): string
    {
        $plan = self::request('POST', '/v1/billing/plans', [
            'product_id'  => $productId,
            'name'        => $name,
            'status'      => 'ACTIVE',
            'billing_cycles' => [[
                'frequency'      => ['interval_unit' => 'MONTH', 'interval_count' => 1],
                'tenure_type'    => 'REGULAR',
                'sequence'       => 1,
                'total_cycles'   => 0,
                'pricing_scheme' => [
                    'fixed_price' => [
                        'value'         => Money::penceToDecimal($priceCents),
                        'currency_code' => $currency,
                    ],
                ],
            ]],
            'payment_preferences' => [
                'auto_bill_outstanding'     => true,
                'payment_failure_threshold' => 3,
            ],
        ]);

        return $plan['id'];
    }

    public static function getSubscription(string $subscriptionId): array
    {
        return self::request('GET', "/v1/billing/subscriptions/{$subscriptionId}");
    }

    public static function cancelSubscription(string $subscriptionId, string $reason): void
    {
        self::request('POST', "/v1/billing/subscriptions/{$subscriptionId}/cancel", ['reason' => $reason]);
    }

    public static function verifyWebhookSignature(array $headers, string $rawBody): bool
    {
        $event = json_decode($rawBody, true);
        if ($event === null) {
            return false;
        }

        $result = self::request('POST', '/v1/notifications/verify-webhook-signature', [
            'auth_algo'         => $headers['PAYPAL-AUTH-ALGO'] ?? '',
            'cert_url'          => $headers['PAYPAL-CERT-URL'] ?? '',
            'transmission_id'   => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
            'transmission_sig'  => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
            'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
            'webhook_id'        => PAYPAL_WEBHOOK_ID,
            'webhook_event'     => $event,
        ]);

        return ($result['verification_status'] ?? '') === 'SUCCESS';
    }
}
