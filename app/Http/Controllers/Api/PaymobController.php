<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Services\PaymobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymobController extends Controller
{
    protected PaymobService $paymobService;

    public function __construct(PaymobService $paymobService)
    {
        $this->paymobService = $paymobService;
    }

    /**
     * Initiate Paymob payment for an order.
     */
    public function initiate(Request $request, string $orderId): JsonResponse
    {
        $order = Order::with(['items.product', 'shippingAddress', 'user'])->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if (in_array($order->payment_status, [Order::PAYMENT_FULL_PAID, Order::PAYMENT_PAID_DEPOSIT])) {
            return response()->json(['message' => 'Order is already paid.'], 400);
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            return response()->json(['message' => 'Cannot pay for a cancelled order.'], 400);
        }

        try {
            $paymentData = $this->paymobService->initiatePayment($order);

            return response()->json([
                'message' => 'Payment initiated successfully.',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'paymob_order_id' => $paymentData['paymob_order_id'],
                'payment_token' => $paymentData['payment_token'],
                'iframe_url' => $paymentData['iframe_url'],
            ]);
        } catch (\Exception $e) {
            Log::error('Paymob initiate error: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'trace' => substr($e->getTraceAsString(), 0, 500),
            ]);

            return response()->json([
                'message' => 'Failed to initialize payment gateway. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Webhook receiver for Paymob transaction notifications (Server-to-Server).
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $hmac = $request->query('hmac', $request->input('hmac'));

        Log::info('Paymob Webhook received', [
            'type' => $payload['type'] ?? 'unknown',
            'has_obj' => isset($payload['obj']),
        ]);

        if (!$this->paymobService->validateHmac($payload, $hmac)) {
            Log::warning('Paymob Webhook rejected: Invalid HMAC signature', ['payload' => $payload]);
            return response()->json(['message' => 'Invalid HMAC signature.'], 403);
        }

        $type = $payload['type'] ?? '';
        $obj = $payload['obj'] ?? [];

        if (strtoupper($type) !== 'TRANSACTION' || empty($obj)) {
            return response()->json(['message' => 'Ignored non-transaction event.'], 200);
        }

        $transactionId = (string) ($obj['id'] ?? '');
        $isSuccess = ($obj['success'] ?? false) === true;
        $paymobOrderId = (string) ($obj['order']['id'] ?? ($obj['order'] ?? ''));
        $merchantOrderId = (string) ($obj['order']['merchant_order_id'] ?? '');

        // Extract base order number if merchant_order_id has a timestamp suffix (e.g. ORD-12345-167890)
        $cleanOrderNumber = explode('-', $merchantOrderId);
        if (count($cleanOrderNumber) >= 3) {
            // ORD-XXXX-YYMMDD-timestamp -> rebuild ORD-XXXX-YYMMDD
            $cleanOrderNumber = implode('-', array_slice($cleanOrderNumber, 0, 3));
        } else {
            $cleanOrderNumber = $merchantOrderId;
        }

        $order = Order::where('paymob_order_id', $paymobOrderId)
            ->orWhere('order_number', $cleanOrderNumber)
            ->orWhere('order_number', $merchantOrderId)
            ->first();

        if (!$order) {
            Log::warning('Paymob Webhook: Order not found for transaction', [
                'paymob_order_id' => $paymobOrderId,
                'merchant_order_id' => $merchantOrderId,
                'transaction_id' => $transactionId,
            ]);
            return response()->json(['message' => 'Order not found in DecoHomz.'], 200);
        }

        DB::beginTransaction();
        try {
            if ($isSuccess) {
                $oldStatus = $order->payment_status;
                $order->update([
                    'payment_status' => Order::PAYMENT_FULL_PAID,
                    'status' => $order->status === Order::STATUS_PENDING ? Order::STATUS_PROCESSING : $order->status,
                    'paymob_transaction_id' => $transactionId,
                    'payment_details' => [
                        'transaction_id' => $transactionId,
                        'source_data' => $obj['source_data'] ?? null,
                        'amount_cents' => $obj['amount_cents'] ?? null,
                        'currency' => $obj['currency'] ?? null,
                        'is_3d_secure' => $obj['is_3d_secure'] ?? false,
                        'card_pan' => $obj['source_data']['pan'] ?? null,
                        'card_type' => $obj['source_data']['sub_type'] ?? null,
                        'paid_at' => now()->toIso8601String(),
                    ],
                ]);

                ActivityLog::orders(
                    $request,
                    'Paymob Payment Successful',
                    "Payment #{$transactionId} completed for Order #{$order->order_number} ({$order->total} EGP). Status changed from {$oldStatus} to full_paid.",
                    $order
                );

                // Save card token if user requested it and token is available
                $cardToken = $obj['source_data']['token'] ?? ($obj['token'] ?? null);
                $shouldSaveCard = $order->notes && str_contains($order->notes, '[SAVE_CARD]');

                if ($cardToken && $shouldSaveCard && $order->user_id) {
                    $maskedPan = '•••• ' . ($obj['source_data']['pan'] ?? '????');
                    $brand = $obj['source_data']['sub_type'] ?? null;

                    \App\Models\SavedCard::updateOrCreate(
                        ['user_id' => $order->user_id, 'card_token' => $cardToken],
                        [
                            'masked_pan' => $maskedPan,
                            'brand' => $brand,
                            'expiry_month' => null,
                            'expiry_year' => null,
                        ]
                    );

                    // Clean the [SAVE_CARD] flag from notes
                    $order->update([
                        'notes' => str_replace('[SAVE_CARD]', '', $order->notes),
                    ]);
                }
            } else {
                $order->update([
                    'paymob_transaction_id' => $transactionId,
                    'payment_details' => [
                        'transaction_id' => $transactionId,
                        'failed' => true,
                        'data_message' => $obj['data']['message'] ?? 'Transaction declined',
                        'attempted_at' => now()->toIso8601String(),
                    ],
                ]);

                ActivityLog::orders(
                    $request,
                    'Paymob Payment Failed',
                    "Payment #{$transactionId} failed for Order #{$order->order_number}. Reason: " . ($obj['data']['message'] ?? 'Transaction declined'),
                    $order
                );
            }

            DB::commit();
            return response()->json(['message' => 'Transaction processed successfully.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error handling Paymob Webhook: ' . $e->getMessage(), [
                'trace' => substr($e->getTraceAsString(), 0, 500),
            ]);
            return response()->json(['message' => 'Internal server error processing webhook.'], 500);
        }
    }

    /**
     * Browser redirect callback from Paymob iframe (Transaction Response Callback).
     */
    public function callback(Request $request)
    {
        $params = $request->all();
        $isSuccess = ($request->query('success') === 'true');
        $paymobOrderId = (string) $request->query('order');
        $transactionId = (string) $request->query('id');

        Log::info('Paymob redirect callback', [
            'success' => $isSuccess,
            'paymob_order_id' => $paymobOrderId,
            'transaction_id' => $transactionId,
        ]);

        $order = Order::where('paymob_order_id', $paymobOrderId)->first();

        // If order not found by paymob_order_id, search by merchant_order_id if present
        if (!$order && $request->has('merchant_order_id')) {
            $order = Order::where('order_number', $request->query('merchant_order_id'))->first();
        }

        if (!$order) {
            return redirect('/')->with('error', 'Order not found.');
        }

        // Validate HMAC
        $isValidHmac = $this->paymobService->validateHmac($params);

        if ($isValidHmac && $isSuccess) {
            if ($order->payment_status !== Order::PAYMENT_FULL_PAID) {
                $order->update([
                    'payment_status' => Order::PAYMENT_FULL_PAID,
                    'status' => $order->status === Order::STATUS_PENDING ? Order::STATUS_PROCESSING : $order->status,
                    'paymob_transaction_id' => $transactionId,
                ]);
            }
            $targetUrl = url("/orders/confirmation/{$order->id}?payment_status=success&txn={$transactionId}");
            $headline = "Payment Successful!";
            $subtext = "Your payment was confirmed. Redirecting to your order confirmation...";
        } else {
            $targetUrl = url("/orders/confirmation/{$order->id}?payment_status=failed&txn={$transactionId}");
            $headline = "Payment Incomplete or Declined";
            $subtext = "Your transaction could not be completed. Redirecting to your order details...";
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{$headline} — DecoHomz</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .card { background: #fff; border-radius: 16px; padding: 32px 24px; max-width: 440px; width: 100%; text-align: center; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }
        .spinner { width: 36px; height: 36px; border: 3px solid #e2e8f0; border-top-color: #c9a96e; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 16px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        h2 { font-size: 19px; color: #0f172a; margin: 0 0 8px; font-weight: 700; }
        p { font-size: 13px; color: #64748b; margin: 0 0 20px; line-height: 1.5; }
        .btn { display: inline-block; background: #0f172a; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; }
    </style>
    <script>
        var targetUrl = "{$targetUrl}";
        try {
            if (window.top && window.top !== window.self) {
                window.top.location.href = targetUrl;
            } else {
                window.location.href = targetUrl;
            }
        } catch (e) {
            window.location.href = targetUrl;
        }
    </script>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h2>{$headline}</h2>
        <p>{$subtext}</p>
        <a href="{$targetUrl}" target="_top" class="btn">Click here to continue</a>
    </div>
</body>
</html>
HTML;

        return response($html, 200)->header('Content-Type', 'text/html');
    }
}
