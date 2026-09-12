<?php

namespace App\Services\Pesapal;

use App\Models\PaymentGatewaySetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around Pesapal API 3.0. Base URLs come from config/services.php
 * (structural, deploy-time constants); credentials, active environment and the
 * registered IPN id all come from the admin-editable PaymentGatewaySetting row.
 * Nothing here is hardcoded.
 */
class PesapalClient
{
    public function __construct(private readonly PaymentGatewaySetting $settings)
    {
    }

    public static function forActiveEnvironment(): self
    {
        return new self(PaymentGatewaySetting::current());
    }

    public function settings(): PaymentGatewaySetting
    {
        return $this->settings;
    }

    private function baseUrl(): string
    {
        return $this->settings->isLive()
            ? config('services.pesapal.live_base_url')
            : config('services.pesapal.sandbox_base_url');
    }

    private function http()
    {
        return Http::timeout((int) config('services.pesapal.timeout', 30))
            ->acceptJson()
            ->asJson();
    }

    private function ensure(\Illuminate\Http\Client\Response $response): array
    {
        $data = $response->json() ?? [];

        if ($response->failed() || !empty($data['error'])) {
            throw new PesapalException(
                $data['error']['message'] ?? $data['message'] ?? 'Pesapal request failed.',
                $data
            );
        }

        return $data;
    }

    public function getToken(): string
    {
        $cacheKey = 'pesapal_token_' . $this->settings->active_environment;
        $ttlBuffer = (int) config('services.pesapal.token_ttl_buffer', 30);

        return Cache::remember($cacheKey, now()->addSeconds(300 - $ttlBuffer), function () {
            $data = $this->ensure(
                $this->http()->post($this->baseUrl() . '/Auth/RequestToken', [
                    'consumer_key' => $this->settings->activeConsumerKey(),
                    'consumer_secret' => $this->settings->activeConsumerSecret(),
                ])
            );

            if (empty($data['token'])) {
                throw new PesapalException('Pesapal did not return an access token.', $data);
            }

            return $data['token'];
        });
    }

    public function registerIpn(string $url, ?string $notificationType = null): array
    {
        $type = $notificationType ?? $this->settings->ipn_notification_type;

        $data = $this->ensure(
            $this->http()
                ->withToken($this->getToken())
                ->post($this->baseUrl() . '/URLSetup/RegisterIPN', [
                    'url' => $url,
                    'ipn_notification_type' => $type,
                ])
        );

        if (!empty($data['ipn_id'])) {
            $this->settings->setActiveIpnId($data['ipn_id']);
        }

        return $data;
    }

    public function submitOrderRequest(array $order): array
    {
        return $this->ensure(
            $this->http()
                ->withToken($this->getToken())
                ->post($this->baseUrl() . '/Transactions/SubmitOrderRequest', $order)
        );
    }

    /**
     * Unlike the other endpoints, Pesapal legitimately returns a populated
     * `error` object alongside a normal `status_code` (e.g. 0/INVALID) for a
     * transaction that simply hasn't completed yet — that is not a request
     * failure, so only a transport-level failure throws here. The caller maps
     * `status_code` (0/1/2/3) to our own transaction status.
     */
    public function getTransactionStatus(string $orderTrackingId): array
    {
        $response = $this->http()
            ->withToken($this->getToken())
            ->get($this->baseUrl() . '/Transactions/GetTransactionStatus', [
                'orderTrackingId' => $orderTrackingId,
            ]);

        if ($response->failed()) {
            $data = $response->json() ?? [];
            throw new PesapalException(
                $data['error']['message'] ?? $data['message'] ?? 'Pesapal request failed.',
                $data
            );
        }

        return $response->json() ?? [];
    }
}
