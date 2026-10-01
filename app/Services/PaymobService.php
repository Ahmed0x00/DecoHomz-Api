<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobService
{
    protected string $apiKey;
    protected string $publicKey;
    protected string $secretKey;
    protected string $hmacSecret;
    protected int $integrationId;
    protected int $iframeId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.paymob.api_key', '');
        $this->publicKey = config('services.paymob.public_key', '');
        $this->secretKey = config('services.paymob.secret_key', '');
        $this->hmacSecret = config('services.paymob.hmac_secret', '');
        $this->integrationId = (int) config('services.paymob.card_integration_id', 5790995);
        $this->iframeId = (int) config('services.paymob.card_iframe_id', 1063247);
        $this->baseUrl = rtrim(config('services.paymob.base_url', 'https://accept.paymob.com'), '/');
    }

    /**
     * Step 1: Obtain Auth Token from Paymob API.
     */
    public function getAuthToken(): string
    {
        return Cache::remember('paymob_auth_token', 3000, function () {
            $response = Http::timeout(15)->post("{$this->baseUrl}/api/auth/tokens", [
                'api_key' => $this->apiKey,
            ]);

            if (!$response->successful()) {
                Log::error('Paymob auth token request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Failed to authenticate with Paymob payment gateway.');
            }

            $token = $response->json('token');
            if (empty($token)) {
                throw new \Exception('Empty token received from Paymob authentication.');
            }

            return $token;
        });
    }

    /**
     * Step 2: Register Order with Paymob.
     */
    public function registerOrder(Order $order, ?string $token = null): int
    {
        $token = $token ?: $this->getAuthToken();
        $amountCents = (int) round($order->total * 100);

        // Merchant order ID: using DecoHomz order number + unique timestamp suffix if re-attempting
        $merchantOrderId = $order->order_number . '-' . time();

        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'name' => mb_substr($item->name, 0, 99),
                'amount_cents' => (string) ((int) round($item->price * 100)),
                'description' => mb_substr($item->variant ?: $item->name, 0, 99),
                'quantity' => (int) $item->quantity,
            ];
        }

        $response = Http::timeout(15)->post("{$this->baseUrl}/api/ecommerce/orders", [
            'auth_token' => $token,
            'delivery_needed' => 'false',
            'amount_cents' => (string) $amountCents,
            'currency' => 'EGP',
            'merchant_order_id' => $merchantOrderId,
            'items' => $items,
        ]);

        if (!$response->successful()) {
            Log::error('Paymob order registration failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to register order with Paymob.');
        }

        $paymobOrderId = (int) $response->json('id');
        if (!$paymobOrderId) {
            throw new \Exception('Invalid order ID received from Paymob.');
        }

        return $paymobOrderId;
    }

    /**
     * Step 3: Generate Payment Key / Token for the iFrame.
     */
    public function generatePaymentKey(Order $order, int $paymobOrderId, ?string $token = null): string
    {
        $token = $token ?: $this->getAuthToken();
        $amountCents = (int) round($order->total * 100);

        $shipping = $order->shippingAddress;
        $user = $order->user;

        $firstName = $shipping?->first_name ?: ($user?->first_name ?: 'Customer');
        $lastName = $shipping?->last_name ?: ($user?->last_name ?: 'DecoHomz');
        $email = $shipping?->email ?: ($user?->email ?: 'customer@decohomz.com');
        $phone = $shipping?->phone ?: ($user?->phone ?: '+201000000000');
        $street = $shipping?->address_line_1 ?: 'NA';
        $building = $shipping?->address_line_2 ?: 'NA';
        $city = $shipping?->governorate ?: ($shipping?->city ?: 'Cairo');
        $state = $shipping?->state ?: $city;

        $billingData = [
            'apartment' => 'NA',
            'email' => $email,
            'floor' => 'NA',
            'first_name' => $firstName,
            'street' => $street,
            'building' => $building,
            'phone_number' => $phone,
            'shipping_method' => 'PKG',
            'postal_code' => $shipping?->postal_code ?: 'NA',
            'city' => $city,
            'country' => 'EG',
            'last_name' => $lastName,
            'state' => $state,
        ];

        $response = Http::timeout(15)->post("{$this->baseUrl}/api/acceptance/payment_keys", [
            'auth_token' => $token,
            'amount_cents' => (string) $amountCents,
            'expiration' => 3600,
            'order_id' => $paymobOrderId,
            'billing_data' => $billingData,
            'currency' => 'EGP',
            'integration_id' => $this->integrationId,
            'lock_order_when_paid' => 'false',
        ]);

        if (!$response->successful()) {
            Log::error('Paymob payment key generation failed', [
                'order_id' => $order->id,
                'paymob_order_id' => $paymobOrderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to generate payment key from Paymob.');
        }

        $paymentToken = $response->json('token');
        if (empty($paymentToken)) {
            throw new \Exception('Invalid payment token received from Paymob.');
        }

        return $paymentToken;
    }

    /**
     * Get the full iFrame payment URL.
     */
    public function getIframeUrl(string $paymentToken): string
    {
        return "{$this->baseUrl}/api/acceptance/iframes/{$this->iframeId}?payment_token={$paymentToken}";
    }

    /**
     * Complete Initiation: Authenticate, Register Order, Generate Key, Build URL.
     */
    public function initiatePayment(Order $order): array
    {
        $token = $this->getAuthToken();
        $paymobOrderId = $this->registerOrder($order, $token);
        $paymentToken = $this->generatePaymentKey($order, $paymobOrderId, $token);
        $iframeUrl = $this->getIframeUrl($paymentToken);

        $order->update([
            'paymob_order_id' => (string) $paymobOrderId,
        ]);

        return [
            'payment_token' => $paymentToken,
            'iframe_url' => $iframeUrl,
            'paymob_order_id' => $paymobOrderId,
        ];
    }

    /**
     * Create a Payment Intention via the Intention API (v1).
     * Returns client_secret for the Pixel SDK embedded card form.
     */
    public function createIntention(Order $order): array
    {
        $amountCents = (int) round($order->total * 100);

        $shipping = $order->shippingAddress;
        $user = $order->user;

        $firstName = $shipping?->first_name ?: ($user?->first_name ?: 'Customer');
        $lastName = $shipping?->last_name ?: ($user?->last_name ?: 'DecoHomz');
        $email = $shipping?->email ?: ($user?->email ?: 'customer@decohomz.com');
        $phone = $shipping?->phone ?: ($user?->phone ?: '+201000000000');
        $street = $shipping?->address_line_1 ?: 'NA';
        $building = $shipping?->address_line_2 ?: 'NA';
        $city = $shipping?->governorate ?: ($shipping?->city ?: 'Cairo');
        $state = $shipping?->state ?: $city;

        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'name' => mb_substr($item->name, 0, 99),
                'amount' => (int) round($item->price * 100),
                'description' => mb_substr($item->variant ?: $item->name, 0, 99),
                'quantity' => (int) $item->quantity,
            ];
        }

        $callbackUrl = config('app.url') . '/payments/paymob/callback';

        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Token ' . $this->secretKey,
            ])
            ->post("{$this->baseUrl}/v1/intention/", [
                'amount' => $amountCents,
                'currency' => 'EGP',
                'payment_methods' => [$this->integrationId],
                'items' => $items,
                'billing_data' => [
                    'apartment' => 'NA',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'street' => $street,
                    'building' => $building,
                    'phone_number' => $phone,
                    'city' => $city,
                    'country' => 'EG',
                    'email' => $email,
                    'floor' => 'NA',
                    'state' => $state,
                ],
                'notification_url' => config('app.url') . '/api/payments/paymob/webhook',
                'redirection_url' => $callbackUrl,
                'extras' => [
                    'merchant_order_id' => $order->order_number,
                ],
            ]);

        if (!$response->successful()) {
            Log::error('Paymob Intention API failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to create payment intention with Paymob.');
        }

        $data = $response->json();
        $clientSecret = $data['client_secret'] ?? null;
        $intentionId = $data['id'] ?? null;

        if (empty($clientSecret)) {
            throw new \Exception('Empty client_secret received from Paymob Intention API.');
        }

        // Store the intention order ID on our order
        $intentionOrderId = $data['intention_order_id'] ?? null;
        if ($intentionOrderId) {
            $order->update([
                'paymob_order_id' => (string) $intentionOrderId,
            ]);
        }

        return [
            'client_secret' => $clientSecret,
            'intention_id' => $intentionId,
            'public_key' => $this->publicKey,
        ];
    }

    /**
     * Validate Paymob HMAC signature from Webhook or Callback.
     */
    public function validateHmac(array $data, ?string $receivedHmac = null): bool
    {
        $hmacSecret = $this->hmacSecret;
        if (empty($hmacSecret)) {
            Log::error('Paymob HMAC validation failed: PAYMOB_HMAC_SECRET not configured');
            return false;
        }

        // If webhook payload with 'obj', extract obj
        if (isset($data['obj']) && is_array($data['obj'])) {
            $obj = $data['obj'];
            $receivedHmac = $receivedHmac ?: ($data['hmac'] ?? request()->query('hmac', ''));

            $extracted = [
                'amount_cents' => $obj['amount_cents'] ?? '',
                'created_at' => $obj['created_at'] ?? '',
                'currency' => $obj['currency'] ?? '',
                'error_occured' => $obj['error_occured'] ?? false,
                'has_parent_transaction' => $obj['has_parent_transaction'] ?? false,
                'id' => $obj['id'] ?? '',
                'integration_id' => $obj['integration_id'] ?? '',
                'is_3d_secure' => $obj['is_3d_secure'] ?? false,
                'is_auth' => $obj['is_auth'] ?? false,
                'is_capture' => $obj['is_capture'] ?? false,
                'is_refunded' => $obj['is_refunded'] ?? false,
                'is_standalone_payment' => $obj['is_standalone_payment'] ?? false,
                'is_voided' => $obj['is_voided'] ?? false,
                'order' => is_array($obj['order'] ?? null) ? ($obj['order']['id'] ?? '') : ($obj['order'] ?? ''),
                'owner' => $obj['owner'] ?? '',
                'pending' => $obj['pending'] ?? false,
                'source_data_pan' => $obj['source_data']['pan'] ?? '',
                'source_data_sub_type' => $obj['source_data']['sub_type'] ?? '',
                'source_data_type' => $obj['source_data']['type'] ?? '',
                'success' => $obj['success'] ?? false,
            ];
        } else {
            // Callback redirect with query parameters
            $receivedHmac = $receivedHmac ?: ($data['hmac'] ?? request()->query('hmac', ''));

            $extracted = [
                'amount_cents' => $data['amount_cents'] ?? '',
                'created_at' => $data['created_at'] ?? '',
                'currency' => $data['currency'] ?? '',
                'error_occured' => $data['error_occured'] ?? false,
                'has_parent_transaction' => $data['has_parent_transaction'] ?? false,
                'id' => $data['id'] ?? '',
                'integration_id' => $data['integration_id'] ?? '',
                'is_3d_secure' => $data['is_3d_secure'] ?? false,
                'is_auth' => $data['is_auth'] ?? false,
                'is_capture' => $data['is_capture'] ?? false,
                'is_refunded' => $data['is_refunded'] ?? false,
                'is_standalone_payment' => $data['is_standalone_payment'] ?? false,
                'is_voided' => $data['is_voided'] ?? false,
                'order' => $data['order'] ?? '',
                'owner' => $data['owner'] ?? '',
                'pending' => $data['pending'] ?? false,
                'source_data_pan' => $data['source_data_pan'] ?? ($data['source_data.pan'] ?? ''),
                'source_data_sub_type' => $data['source_data_sub_type'] ?? ($data['source_data.sub_type'] ?? ''),
                'source_data_type' => $data['source_data_type'] ?? ($data['source_data.type'] ?? ''),
                'success' => $data['success'] ?? false,
            ];
        }

        ksort($extracted);

        $concatenated = '';
        foreach ($extracted as $key => $val) {
            if (is_bool($val)) {
                $concatenated .= $val ? 'true' : 'false';
            } else {
                $concatenated .= (string) $val;
            }
        }

        $calculatedHmac = hash_hmac('sha512', $concatenated, $hmacSecret);

        if (empty($receivedHmac)) {
            Log::warning('Paymob HMAC empty in request');
            return false;
        }

        $isValid = hash_equals(strtolower($calculatedHmac), strtolower($receivedHmac));

        if (!$isValid) {
            Log::warning('Paymob HMAC mismatch', [
                'calculated' => $calculatedHmac,
                'received' => $receivedHmac,
                'concatenated' => $concatenated,
            ]);
        }

        return $isValid;
    }
}
