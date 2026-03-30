<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promotion;
use Carbon\Carbon;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a sample business and outlet (assuming they exist)
        $business = \App\Models\Business::first();
        $outlet = \App\Models\PosOutlet::first();

        if (!$business) {
            $this->command->warn('Please seed businesses first!');
            return;
        }

        // outlet_id is nullable, so we can proceed without it
        $outletId = $outlet ? $outlet->id : null;

        $promotions = [
            // Percentage Discounts - Order Level
            [
                'business_id' => $business->id,
                'outlet_id' => $outletId,
                'name' => 'Summer Sale 20% Off',
                'code' => 'SUMMER20',
                'type' => 'percentage',
                'value' => 20.00,
                'applies_to' => 'order',
                'min_order_amount' => 50.00,
                'max_uses' => 100,
                'uses_count' => 0,
                'valid_from' => Carbon::now()->format('Y-m-d'),
                'valid_to' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'status' => 'active',
            ],
            [
                'business_id' => $business->id,
                'outlet_id' => $outletId,
                'name' => 'VIP Member 15% Discount',
                'code' => 'VIP15',
                'type' => 'percentage',
                'value' => 15.00,
                'applies_to' => 'member',
                'min_order_amount' => 0.00,
                'max_uses' => null,
                'uses_count' => 0,
                'valid_from' => Carbon::now()->format('Y-m-d'),
                'valid_to' => Carbon::now()->addYear()->format('Y-m-d'),
                'status' => 'active',
            ],
            [
                'business_id' => $business->id,
                'outlet_id' => $outletId,
                'name' => 'Weekend Special 10%',
                'code' => 'WEEKEND10',
                'type' => 'percentage',
                'value' => 10.00,
                'applies_to' => 'order',
                'min_order_amount' => 30.00,
                'max_uses' => 200,
                'uses_count' => 15,
                'valid_from' => Carbon::now()->format('Y-m-d'),
                'valid_to' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                'active_days' => json_encode(['Saturday', 'Sunday']),
                'status' => 'active',
            ],

            // Fixed Amount Discounts
            [
                'business_id' => $business->id,
                'outlet_id' => $outletId,
                'name' => 'New Customer $25 Off',
                'code' => 'WELCOME25',
                'type' => 'fixed_amount',
                'value' => 25.00,
                'applies_to' => 'order',
                'min_order_amount' => 100.00,
                'max_uses' => 50,
                'uses_count' => 0,
                'valid_from' => Carbon::now()->format('Y-m-d'),
                'valid_to' => Carbon::now()->addMonths(12)->format('Y-m-d'),
                'status' => 'active',
            ],
            [
                'business_id' => $business->id,
                'outlet_id' => $outletId,
                'name' => 'Loyalty Reward $50',
                'code' => 'LOYAL50',
                'type' => 'fixed_amount',
                'value' => 50.00,
                'applies_to' => 'member',
                'min_order_amount' => 200.00,
                'max_uses' => null,
                'uses_count' => 8,
                'valid_from' => Carbon::now()->format('Y-m-d'),
                'valid_to' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                'status' => 'active',
            ],
            [
                'business_id' => $business->id,
                'outlet_id' => $outletId,
                'name' => 'Flash Sale $10',
                'code' => 'FLASH10',
                'type' => 'fixed_amount',
                'value' => 10.00,
                'applies_to' => 'order',
                'min_order_amount' => 40.00,
                'max_uses' => 500,
                'uses_count' => 234,
                'valid_from' => Carbon::now()->format('Y-m-d'),
                'valid_to' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'status' => 'active',
            ],

            // Buy X Get Y Promotions
            [
                'business_id' => $business->id,
                'outlet_id' => $outletId,
                'name' => 'Buy 2 Get 1 Free',
                'code' => 'BUY2GET1',
                'type' => 'buy_x_get_y',
                'value' => 100.00, // 100% off = free
                'applies_to' => 'order',
                'min_order_amount' => 0.00,
                'max_uses' => 100,
                'uses_count' => 12,
                'valid_from' => Carbon::now()->format('Y-m-d'),
                'valid_to' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'status' => 'active',
            ],
            [
                'business_id' => $business->id,
                'outlet_id' => $outletId,
                'name' => 'Buy 3 Get 50% Off',
                'code' => 'BUY3HALF',
                'type' => 'buy_x_get_y',
                'value' => 50.00,
                'applies_to' => 'order',
                'min_order_amount' => 0.00,
                'max_uses' => 150,
                'uses_count' => 28,
                'valid_from' => Carbon::now()->format('Y-m-d'),
                'valid_to' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'status' => 'active',
            ],

            // Expired Promotion
            [
                'business_id' => $business->id,
                'outlet_id' => $outletId,
                'name' => 'Expired Holiday Sale',
                'code' => 'XMAS30',
                'type' => 'percentage',
                'value' => 30.00,
                'applies_to' => 'order',
                'min_order_amount' => 50.00,
                'max_uses' => 200,
                'uses_count' => 156,
                'valid_from' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'valid_to' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'status' => 'expired',
            ],

            // Future Promotion
            [
                'business_id' => $business->id,
                'outlet_id' => $outletId,
                'name' => 'Upcoming Black Friday',
                'code' => 'BFRIDAY40',
                'type' => 'percentage',
                'value' => 40.00,
                'applies_to' => 'order',
                'min_order_amount' => 100.00,
                'max_uses' => 300,
                'uses_count' => 0,
                'valid_from' => Carbon::now()->addMonths(1)->format('Y-m-d'),
                'valid_to' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'status' => 'active',
            ],

            // High Value Discount
            [
                'business_id' => $business->id,
                'outlet_id' => $outletId,
                'name' => 'High Roller 25% Discount',
                'code' => 'VIP25',
                'type' => 'percentage',
                'value' => 25.00,
                'applies_to' => 'order',
                'min_order_amount' => 500.00,
                'max_uses' => null,
                'uses_count' => 5,
                'valid_from' => Carbon::now()->format('Y-m-d'),
                'valid_to' => Carbon::now()->addYear()->format('Y-m-d'),
                'status' => 'active',
            ],

            // Time-based Promotion
            [
                'business_id' => $business->id,
                'outlet_id' => $outletId,
                'name' => 'Early Bird Special',
                'code' => 'EARLYBIRD',
                'type' => 'fixed_amount',
                'value' => 15.00,
                'applies_to' => 'order',
                'min_order_amount' => 60.00,
                'max_uses' => 100,
                'uses_count' => 42,
                'valid_from' => Carbon::now()->format('Y-m-d'),
                'valid_to' => Carbon::now()->addMonths(4)->format('Y-m-d'),
                'active_start_time' => '06:00:00',
                'active_end_time' => '10:00:00',
                'status' => 'active',
            ],
        ];

        foreach ($promotions as $promotion) {
            Promotion::create($promotion);
        }

        $this->command->info('Seeded ' . count($promotions) . ' sample promotions');
    }
}
