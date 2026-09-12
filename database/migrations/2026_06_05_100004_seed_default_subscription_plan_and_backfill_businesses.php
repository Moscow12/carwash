<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $planId = (string) Str::uuid();

        DB::table('subscription_plans')->insert([
            'id' => $planId,
            'name' => 'Free Trial',
            'business_type' => null,
            'fee_amount' => 0,
            'currency' => 'TZS',
            'billing_interval' => 'monthly',
            'description' => 'Default plan assigned to businesses that existed before subscription billing was introduced.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $businessIds = DB::table('businesses')->pluck('id');

        foreach ($businessIds as $businessId) {
            $exists = DB::table('business_subscriptions')->where('business_id', $businessId)->exists();

            if (!$exists) {
                DB::table('business_subscriptions')->insert([
                    'id' => (string) Str::uuid(),
                    'business_id' => $businessId,
                    'plan_id' => $planId,
                    'status' => 'active',
                    'starts_at' => now(),
                    'expires_at' => now()->addYears(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('subscription_plans')->where('name', 'Free Trial')->delete();
    }
};
