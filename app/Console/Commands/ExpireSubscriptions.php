<?php

namespace App\Console\Commands;

use App\Models\BusinessSubscription;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Mark active business subscriptions as expired once their expiry date has passed.';

    public function handle(): int
    {
        $count = BusinessSubscription::where('status', 'active')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$count} subscription(s).");

        return self::SUCCESS;
    }
}
