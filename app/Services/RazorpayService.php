<?php

namespace App\Services;

use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    /**
     * @var Api
     */
    protected $api;

    public function __construct()
    {
        $keyId = config('services.razorpay.key');
        $keySecret = config('services.razorpay.secret');

        if (!$keyId || !$keySecret) {
            Log::warning('Razorpay API keys are not set in the environment.');
        } else {
            $this->api = new Api($keyId, $keySecret);
        }
    }

    /**
     * Create a Razorpay Order
     *
     * @param float $amount Amount in INR (or base currency)
     * @param string|null $receipt Optional custom receipt ID
     * @param string $currency Currency (default INR)
     * @return array
     */
    public function createOrder($amount, $receipt = null, $currency = 'INR')
    {
        try {
            // Razorpay accepts amount in paise/cents (multiply by 100)
            $orderData = [
                'receipt'         => $receipt ?? 'receipt_' . uniqid(),
                'amount'          => $amount * 100,
                'currency'        => $currency,
                'payment_capture' => 1 // Auto-capture the payment
            ];

            $razorpayOrder = $this->api->order->create($orderData);

            return [
                'success' => true,
                'order_id' => $razorpayOrder['id'],
                'amount' => $amount,
                'currency' => $currency,
                'receipt' => $orderData['receipt']
            ];
        } catch (Exception $e) {
            Log::error('Razorpay Order Creation Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify the Razorpay Payment Signature
     * 
     * This is required after the client-side checkout finishes successfully
     * to ensure the payload was not tampered with.
     *
     * @param string $razorpayOrderId
     * @param string $razorpayPaymentId
     * @param string $razorpaySignature
     * @return bool
     */
    public function verifyPaymentSignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)
    {
        try {
            $attributes = [
                'razorpay_order_id'   => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature'  => $razorpaySignature
            ];

            $this->api->utility->verifyPaymentSignature($attributes);
            
            return true;
        } catch (Exception $e) {
            Log::error('Razorpay Signature Verification Failed: ' . $e->getMessage());
            return false;
        }
    }
}
