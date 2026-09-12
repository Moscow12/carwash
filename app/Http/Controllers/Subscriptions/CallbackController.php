<?php

namespace App\Http\Controllers\Subscriptions;

use App\Http\Controllers\Controller;
use App\Services\Pesapal\PesapalClient;
use App\Services\Pesapal\SubscriptionPaymentProcessor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CallbackController extends Controller
{
    public function __invoke(Request $request): View
    {
        $orderTrackingId = $request->query('OrderTrackingId');

        $transaction = null;
        $error = null;

        if ($orderTrackingId) {
            try {
                $processor = new SubscriptionPaymentProcessor(PesapalClient::forActiveEnvironment());
                $transaction = $processor->confirm($orderTrackingId);
                $transaction->update(['callback_received_at' => now()]);
            } catch (\Throwable $e) {
                $error = 'We could not confirm your payment status. Please contact support if you were charged.';
            }
        } else {
            $error = 'Missing payment reference.';
        }

        return view('subscriptions.callback', [
            'transaction' => $transaction,
            'error' => $error,
        ]);
    }
}
