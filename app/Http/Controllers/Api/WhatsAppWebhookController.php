<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class WhatsAppWebhookController extends Controller
{
    /**
     * Webhook verification (GET) — Meta sends this to verify your endpoint.
     */
    public function verify(Request $request)
    {
        $verifyToken = config('services.whatsapp.verify_token');

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WhatsApp webhook verified successfully.');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('WhatsApp webhook verification failed.', [
            'mode' => $mode,
            'token_match' => false,
            'ip' => request()->ip(),
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Webhook handler (POST) — receives incoming messages from WhatsApp.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('WhatsApp webhook received', ['payload' => $payload]);

        // Extract the message from the webhook payload
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) {
            return response()->json(['status' => 'ok']);
        }

        $changes = $entry['changes'][0] ?? null;
        if (!$changes) {
            return response()->json(['status' => 'ok']);
        }

        $value = $changes['value'] ?? null;
        $messages = $value['messages'] ?? [];

        foreach ($messages as $message) {
            $from = $message['from'] ?? null; // Customer's phone number
            $messageBody = $message['text']['body'] ?? null;
            $messageType = $message['type'] ?? null;

            if ($messageType !== 'text' || !$messageBody || !$from) {
                continue;
            }

            // Try to extract order number from the message (format: #DHXXXXXXX)
            if (preg_match('/#(DH[A-Z0-9]+)/i', $messageBody, $matches)) {
                $orderNumber = strtoupper($matches[1]);
                $this->handleOrderMessage($from, $orderNumber);
            } else {
                // Generic auto-reply for non-order messages
                $this->sendGenericReply($from);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle an incoming message that contains an order number.
     * Looks up the order, calculates deposit, and sends a confirmation reply.
     */
    private function handleOrderMessage(string $customerPhone, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items', 'shippingAddress'])
            ->first();

        if (!$order) {
            $this->sendMessage($customerPhone, "Sorry, we couldn't find order *#{$orderNumber}*. Please double-check your order number and try again.");
            return;
        }

        // Calculate deposit (10% of total, minimum EGP 500)
        $total = floatval($order->total);
        $deposit = max(500, round($total * 0.1));

        // Build the confirmation reply
        $reply = "✅ *Order #{$orderNumber} Received!*\n\n";
        $reply .= "Thank you for your order! Here are your details:\n\n";

        // List items
        $reply .= "*Order Items:*\n";
        foreach ($order->items as $item) {
            $itemTotal = floatval($item->price) * intval($item->quantity);
            $reply .= "• {$item->product_name} (x{$item->quantity}) — EGP " . number_format($itemTotal) . "\n";
        }

        $reply .= "\n*Total: EGP " . number_format($total) . "*\n";
        $reply .= "*Required Deposit: EGP " . number_format($deposit) . "*\n\n";

        $reply .= "💳 *Payment Options:*\n";
        $reply .= "• *InstaPay:* +20114289021\n";
        $reply .= "• *Vodafone Cash:* +201023234\n\n";

        $reply .= "Please send the deposit of *EGP " . number_format($deposit) . "* to one of the above and share the transfer screenshot here.\n\n";
        $reply .= "Once confirmed, we'll start processing your order right away! 🚚";

        $this->sendMessage($customerPhone, $reply);

        Log::info("WhatsApp auto-reply sent for order #{$orderNumber}", [
            'customer' => $customerPhone,
            'deposit' => $deposit,
        ]);
    }

    /**
     * Send a generic reply for messages that don't contain an order number.
     */
    private function sendGenericReply(string $customerPhone)
    {
        $reply = "👋 *Welcome to DecoHomz!*\n\n";
        $reply .= "Thank you for reaching out. If you've placed an order, please send us the order message from the website and we'll confirm it right away.\n\n";
        $reply .= "For any questions, our team will get back to you shortly!";

        $this->sendMessage($customerPhone, $reply);
    }

    /**
     * Send a WhatsApp message using the Meta Cloud API.
     */
    private function sendMessage(string $to, string $text)
    {
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $accessToken = config('services.whatsapp.access_token');

        if (!$phoneNumberId || !$accessToken) {
            Log::error('WhatsApp API credentials not configured.');
            return;
        }

        $url = "https://graph.facebook.com/v21.0/{$phoneNumberId}/messages";

        try {
            $response = Http::withToken($accessToken)->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $text,
                ],
            ]);

            if ($response->failed()) {
                Log::error('WhatsApp message send failed', [
                    'to' => $to,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp message send exception', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
