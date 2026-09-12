<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySetting extends Model
{
    use HasUuids;

    protected $table = 'payment_gateway_settings';

    protected $fillable = [
        'active_environment',
        'test_consumer_key',
        'test_consumer_secret',
        'test_ipn_id',
        'live_consumer_key',
        'live_consumer_secret',
        'live_ipn_id',
        'ipn_notification_type',
        'last_test_connection_at',
        'last_test_connection_ok',
        'last_test_connection_message',
    ];

    protected $casts = [
        'test_consumer_key' => 'encrypted',
        'test_consumer_secret' => 'encrypted',
        'live_consumer_key' => 'encrypted',
        'live_consumer_secret' => 'encrypted',
        'last_test_connection_at' => 'datetime',
        'last_test_connection_ok' => 'boolean',
    ];

    /** There is exactly one settings row for the whole platform. */
    public static function current(): self
    {
        return self::query()->firstOrCreate([], self::getDefaults());
    }

    public static function getDefaults(): array
    {
        return [
            'active_environment' => 'test',
            'ipn_notification_type' => 'GET',
        ];
    }

    public function isLive(): bool
    {
        return $this->active_environment === 'live';
    }

    public function activeConsumerKey(): ?string
    {
        return $this->isLive() ? $this->live_consumer_key : $this->test_consumer_key;
    }

    public function activeConsumerSecret(): ?string
    {
        return $this->isLive() ? $this->live_consumer_secret : $this->test_consumer_secret;
    }

    public function activeIpnId(): ?string
    {
        return $this->isLive() ? $this->live_ipn_id : $this->test_ipn_id;
    }

    public function setActiveIpnId(string $ipnId): void
    {
        $field = $this->isLive() ? 'live_ipn_id' : 'test_ipn_id';
        $this->update([$field => $ipnId]);
    }
}
