<?php

namespace Aghfatehi\Tabby\Controllers;

use Aghfatehi\Tabby\Facades\Tabby;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class TabbyController extends Controller
{
    public function pay(Request $request)
    {
        Log::info('Initiating Tabby checkout...');

        $amount = $request->input('amount', 0);
        $currency = config('tabby.currency', 'SAR');
        $user = $request->user();

        $firstName = $user?->name ?? $request->input('first_name', 'Customer');
        $lastName = $user?->name ?? 'Customer';
        $email = $user?->email ?? $request->input('email', 'otp.success@tabby.ai');
        $phone = $user?->phone ?? $request->input('phone', '500000001');

        $requestBody = [
            'payment' => [
                'amount' => (string) $amount,
                'currency' => $currency,
                'description' => $request->input('description', 'Order payment'),
                'buyer' => [
                    'phone' => $phone,
                    'email' => $email,
                    'name' => $user?->name ?? 'Customer',
                    'dob' => $request->input('dob', date('Y-m-d')),
                ],
                'buyer_history' => [
                    'registered_since' => $user?->created_at?->format('Y-m-d\TH:i:s\Z') ?? now()->format('Y-m-d\TH:i:s\Z'),
                    'loyalty_level' => 0,
                    'wishlist_count' => 0,
                    'is_social_networks_connected' => false,
                    'is_phone_number_verified' => !empty($phone),
                    'is_email_verified' => !empty($email),
                ],
                'order' => [
                    'tax_amount' => $request->input('tax_amount', '0.00'),
                    'shipping_amount' => $request->input('shipping_amount', '0.00'),
                    'discount_amount' => $request->input('discount_amount', '0.00'),
                    'updated_at' => now()->format('Y-m-d\TH:i:s\Z'),
                    'reference_id' => uniqid('tabby_', true),
                    'items' => $request->input('items', [
                        [
                            'title' => 'Order Payment',
                            'description' => 'Payment for order',
                            'quantity' => 1,
                            'unit_price' => (string) $amount,
                            'discount_amount' => '0.00',
                            'reference_id' => uniqid('item_'),
                            'category' => 'Digital Service',
                        ],
                    ]),
                ],
                'shipping_address' => [
                    'city' => $request->input('city', 'Riyadh'),
                    'address' => $request->input('address', 'Default Address'),
                    'zip' => $request->input('zip', '12345'),
                ],
                'meta' => [
                    'order_id' => $request->input('order_id', uniqid('ord_')),
                    'customer' => (string) ($user?->id ?? 'guest'),
                ],
            ],
            'lang' => config('tabby.language', 'en'),
            'merchant_code' => config('tabby.merchant_code', ''),
            'merchant_urls' => [
                'success' => route('tabby.callback'),
                'cancel' => route('tabby.cancel'),
                'failure' => route('tabby.failure'),
            ],
        ];

        try {
            $response = Tabby::createCheckout($requestBody);
            Log::info('Tabby Checkout Response:', $response);

            if (isset($response['error']) || isset($response['errors'])) {
                $errorMessage = $response['message'] ?? ($response['errors'][0]['message'] ?? 'Payment failed');
                return redirect()->back()->withErrors(['error' => $errorMessage]);
            }

            if (isset($response['payment']['id'])) {
                Session::put('tabby_payment_id', $response['payment']['id']);
                Session::put('tabby_session_id', $response['id'] ?? null);

                $webUrl = $response['configuration']['available_products']['installments'][0]['web_url']
                    ?? $response['configuration']['available_products']['pay_by_installments']['web_url']
                    ?? null;

                if ($webUrl) {
                    return Redirect::to($webUrl);
                }
            }

            return redirect()->back()->withErrors(['error' => 'Payment failed: No checkout URL returned']);
        } catch (\Throwable $e) {
            Log::error('Tabby Checkout Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function callback(Request $request)
    {
        Log::info('Tabby Callback:', $request->all());

        $paymentId = $request->input('payment_id') ?? Session::get('tabby_payment_id');

        if (!$paymentId) {
            return redirect()->route('home')->withErrors(['error' => __('Payment verification failed')]);
        }

        try {
            $response = Tabby::getPayment($paymentId);
            Log::info('Tabby Payment Status:', $response);

            $status = strtolower($response['status'] ?? '');

            if (in_array($status, ['authorized', 'captured'], true)) {
                Session::put('tabby_payment_success', true);
                Session::put('tabby_payment_response', json_encode($response));

                return redirect()->route('home')->with('success', __('Payment completed successfully'));
            }

            if ($status === 'rejected') {
                return redirect()->route('home')->with('warning', __('Payment was rejected'));
            }

            return redirect()->route('home')->withErrors(['error' => __('Payment was not completed')]);
        } catch (\Throwable $e) {
            Log::error('Tabby Callback Error: ' . $e->getMessage());
            return redirect()->route('home')->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(Request $request)
    {
        Log::info('Tabby Payment Cancelled');
        return redirect()->route('home')->with('warning', __('Payment was cancelled'));
    }

    public function failure(Request $request)
    {
        Log::info('Tabby Payment Failed');
        return redirect()->route('home')->withErrors(['error' => __('Payment failed')]);
    }

    public function webhook(Request $request)
    {
        Log::info('Tabby Webhook Received:', $request->all());

        $event = $request->input('event_type', '');
        $paymentId = $request->input('payment_id', '');
        $status = $request->input('status', '');

        Log::info("Tabby Webhook - Event: {$event}, Payment: {$paymentId}, Status: {$status}");

        switch ($event) {
            case 'payment_authorized':
            case 'payment_captured':
                Log::info("Tabby payment {$event} for {$paymentId}");
                break;

            case 'payment_failed':
            case 'payment_rejected':
                Log::warning("Tabby payment {$event} for {$paymentId}");
                break;

            default:
                Log::warning("Unknown Tabby webhook event: {$event}");
        }

        return response()->json(['success' => true]);
    }

    public function capture(Request $request)
    {
        $paymentId = $request->input('payment_id');
        $amount = $request->input('amount');

        if (!$paymentId || !$amount) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields: payment_id, amount',
            ], 400);
        }

        try {
            $response = Tabby::capturePayment($paymentId, (string) $amount);

            if (isset($response['id'])) {
                return response()->json([
                    'success' => true,
                    'capture_id' => $response['id'],
                    'message' => 'Payment captured successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response['message'] ?? 'Capture failed',
            ], 400);
        } catch (\Throwable $e) {
            Log::error('Tabby Capture Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function refund(Request $request)
    {
        $paymentId = $request->input('payment_id');
        $amount = $request->input('amount');

        if (!$paymentId || !$amount) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields: payment_id, amount',
            ], 400);
        }

        try {
            $response = Tabby::refundPayment($paymentId, (string) $amount);

            if (isset($response['id'])) {
                return response()->json([
                    'success' => true,
                    'refund_id' => $response['id'],
                    'message' => 'Refund processed successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response['message'] ?? 'Refund failed',
            ], 400);
        } catch (\Throwable $e) {
            Log::error('Tabby Refund Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPaymentDetails(string $paymentId)
    {
        try {
            $response = Tabby::getPayment($paymentId);
            return response()->json($response);
        } catch (\Throwable $e) {
            Log::error('Tabby Get Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
