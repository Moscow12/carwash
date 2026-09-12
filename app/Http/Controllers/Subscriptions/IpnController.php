<?php

namespace App\Http\Controllers\Subscriptions;

use App\Http\Controllers\Controller;
use App\Services\Pesapal\PesapalClient;
use App\Services\Pesapal\SubscriptionPaymentProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IpnController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $orderTrackingId = $request->input('OrderTrackingId') ?? $request->query('OrderTrackingId');
        $merchantReference = $request->input('OrderMerchantReference') ?? $request->query('OrderMerchantReference');

        if (!$orderTrackingId) {
            return response()->json([
                'orderNotificationType' => 'IPNCHANGE',
                'orderTrackingId' => $orderTrackingId,
                'orderMerchantReference' => $merchantReference,
                'status' => 500,
            ]);
        }

        try {
            $processor = new SubscriptionPaymentProcessor(PesapalClient::forActiveEnvironment());
            $transaction = $processor->confirm($orderTrackingId);
            $transaction->update(['ipn_received_at' => now()]);

            return response()->json([
                'orderNotificationType' => 'IPNCHANGE',
                'orderTrackingId' => $orderTrackingId,
                'orderMerchantReference' => $transaction->merchant_reference,
                'status' => 200,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'orderNotificationType' => 'IPNCHANGE',
                'orderTrackingId' => $orderTrackingId,
                'orderMerchantReference' => $merchantReference,
                'status' => 500,
            ]);
        }
    }
}
