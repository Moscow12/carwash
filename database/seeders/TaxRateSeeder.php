<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxRate;
use App\Models\Business;
use Illuminate\Support\Str;

class TaxRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businesses = Business::all();

        if ($businesses->isEmpty()) {
            $this->command->warn('No businesses found. Please create businesses first.');
            return;
        }

        foreach ($businesses as $business) {
            // Check if tax rates already exist for this business
            if (TaxRate::where('business_id', $business->id)->exists()) {
                $this->command->info("Tax rates already exist for business: {$business->name}");
                continue;
            }

            // VAT 18% (Tanzania standard)
            TaxRate::create([
                'id' => Str::uuid(),
                'business_id' => $business->id,
                'name' => 'VAT 18%',
                'code' => 'VAT',
                'rate' => 0.1800,
                'is_inclusive' => false,
                'applies_to' => 'all',
                'is_default' => true,
                'status' => 'active',
            ]);

            // Service Charge 10%
            TaxRate::create([
                'id' => Str::uuid(),
                'business_id' => $business->id,
                'name' => 'Service Charge 10%',
                'code' => 'SC',
                'rate' => 0.1000,
                'is_inclusive' => false,
                'applies_to' => 'food',
                'is_default' => false,
                'status' => 'active',
            ]);

            // Tourism Levy 1.5%
            TaxRate::create([
                'id' => Str::uuid(),
                'business_id' => $business->id,
                'name' => 'Tourism Levy 1.5%',
                'code' => 'LEVY',
                'rate' => 0.0150,
                'is_inclusive' => false,
                'applies_to' => 'accommodation',
                'is_default' => false,
                'status' => 'active',
            ]);

            // Tax Exempt
            TaxRate::create([
                'id' => Str::uuid(),
                'business_id' => $business->id,
                'name' => 'Tax Exempt',
                'code' => 'EXEMPT',
                'rate' => 0.0000,
                'is_inclusive' => false,
                'applies_to' => 'all',
                'is_default' => false,
                'status' => 'active',
            ]);

            $this->command->info("Created tax rates for business: {$business->name}");
        }

        $this->command->info('Tax rates seeding completed successfully!');
    }
}
