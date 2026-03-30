<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $business = \App\Models\Business::first();

        if (!$business) {
            $this->command->warn('Please seed businesses first!');
            return;
        }

        $paymentMethods = [
            [
                'name' => 'Cash',
                'description' => 'Cash payment',
            ],
            [
                'name' => 'Room Charge',
                'description' => 'Charge to hotel room folio for deferred payment',
            ],
            [
                'name' => 'Tab',
                'description' => 'Bar tab for deferred payment',
            ],
            [
                'name' => 'Credit Card',
                'description' => 'Credit or debit card payment',
            ],
            [
                'name' => 'Mobile Payment',
                'description' => 'Mobile wallet payment (e.g., Apple Pay, Google Pay)',
            ],
            [
                'name' => 'Bank Transfer',
                'description' => 'Direct bank transfer payment',
            ],
            [
                'name' => 'Check',
                'description' => 'Payment by check',
            ],
        ];

        foreach ($paymentMethods as $method) {
            $exists = DB::table('payment_methods')
                ->where('business_id', $business->id)
                ->where('name', $method['name'])
                ->exists();

            if (!$exists) {
                DB::table('payment_methods')->insert([
                    'id' => Str::uuid(),
                    'business_id' => $business->id,
                    'name' => $method['name'],
                    'description' => $method['description'],
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->command->info('Added payment method: ' . $method['name']);
            } else {
                $this->command->comment('Payment method already exists: ' . $method['name']);
            }
        }

        $this->command->info('Payment methods seeding completed!');
    }
}
