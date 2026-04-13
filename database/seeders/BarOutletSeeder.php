<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\BarProfile;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Support\Str;

class BarOutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businesses = Business::whereIn('type', ['hotel', 'restaurant', 'bar', 'both'])->get();

        if ($businesses->isEmpty()) {
            $this->command->warn('No hotel/restaurant/bar businesses found. Please create businesses first.');
            return;
        }

        foreach ($businesses as $business) {
            // Check if bar outlet already exists
            $barOutlet = PosOutlet::where('business_id', $business->id)->where('type', 'bar')->first();

            if ($barOutlet) {
                $this->command->info("Bar outlet already exists for business: {$business->name}");
            } else {
                // Create Bar Outlet
                $barOutlet = PosOutlet::create([
                'id' => Str::uuid(),
                'business_id' => $business->id,
                'branch_id' => null,
                'name' => 'Main Bar',
                'type' => 'bar',
                'open_time' => '17:00',
                'close_time' => '03:00',
                'status' => 'active',
            ]);

                $this->command->info("Created bar outlet for business: {$business->name}");
            }

            // Create Bar Profile if it doesn't exist
            $barProfile = BarProfile::where('outlet_id', $barOutlet->id)->first();
            if (!$barProfile) {
                BarProfile::create([
                'id' => Str::uuid(),
                'business_id' => $business->id,
                'outlet_id' => $barOutlet->id,
                'enforce_age_check' => true,
                'min_drinking_age' => 18,
                'tab_enabled' => true,
                'tab_credit_limit' => 500000.00,
                'happy_hour_enabled' => true,
                'happy_hour_start' => '17:00',
                'happy_hour_end' => '19:00',
                'happy_hour_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                'happy_hour_discount_pct' => 20.00,
                'bottle_service_enabled' => true,
                'receipt_message' => 'Thank you for visiting our bar! Drink responsibly.',
                'status' => 'active',
                ]);

                $this->command->info("Created bar profile for outlet: {$barOutlet->name}");
            } else {
                $this->command->info("Bar profile already exists for outlet: {$barOutlet->name}");
            }

            // Create Beverage Categories
            $categories = [
                ['name' => 'Spirits', 'description' => 'Premium spirits and liquors'],
                ['name' => 'Beers', 'description' => 'Local and imported beers'],
                ['name' => 'Wines', 'description' => 'Red, white, and sparkling wines'],
                ['name' => 'Cocktails', 'description' => 'Classic and signature cocktails'],
                ['name' => 'Soft Drinks', 'description' => 'Non-alcoholic beverages'],
                ['name' => 'Bar Snacks', 'description' => 'Light bites and appetizers'],
            ];

            foreach ($categories as $categoryData) {
                $category = MenuCategory::firstOrCreate([
                    'outlet_id' => $barOutlet->id,
                    'name' => $categoryData['name'],
                ], [
                    'id' => Str::uuid(),
                    'description' => $categoryData['description'],
                    'status' => 'active',
                    'sort_order' => 0,
                ]);

                // Create sample menu items for each category
                $this->createMenuItems($barOutlet, $category);
            }

            $this->command->info("Created menu categories and items for: {$barOutlet->name}");
        }

        $this->command->info('Bar outlet seeding completed successfully!');
    }

    /**
     * Create sample menu items for a category
     */
    private function createMenuItems($outlet, $category)
    {
        $items = [];

        switch ($category->name) {
            case 'Spirits':
                $items = [
                    ['name' => 'Vodka Shot', 'price' => 5000, 'description' => 'Premium vodka shot (30ml)'],
                    ['name' => 'Whiskey Shot', 'price' => 8000, 'description' => 'Single malt whiskey shot (30ml)'],
                    ['name' => 'Rum Shot', 'price' => 6000, 'description' => 'Caribbean rum shot (30ml)'],
                    ['name' => 'Tequila Shot', 'price' => 7000, 'description' => 'Silver tequila shot (30ml)'],
                    ['name' => 'Gin Shot', 'price' => 6500, 'description' => 'London dry gin shot (30ml)'],
                ];
                break;

            case 'Beers':
                $items = [
                    ['name' => 'Local Beer (Bottle)', 'price' => 3000, 'description' => '330ml bottle'],
                    ['name' => 'Premium Beer (Bottle)', 'price' => 5000, 'description' => '330ml imported bottle'],
                    ['name' => 'Draft Beer (Pint)', 'price' => 4000, 'description' => 'Fresh draft beer (500ml)'],
                    ['name' => 'Craft Beer', 'price' => 6000, 'description' => 'Artisan craft beer (330ml)'],
                ];
                break;

            case 'Wines':
                $items = [
                    ['name' => 'House Red Wine (Glass)', 'price' => 8000, 'description' => 'Glass of house red (150ml)'],
                    ['name' => 'House White Wine (Glass)', 'price' => 8000, 'description' => 'Glass of house white (150ml)'],
                    ['name' => 'Premium Red Wine (Bottle)', 'price' => 50000, 'description' => 'Full bottle premium red (750ml)'],
                    ['name' => 'Premium White Wine (Bottle)', 'price' => 50000, 'description' => 'Full bottle premium white (750ml)'],
                    ['name' => 'Champagne (Bottle)', 'price' => 80000, 'description' => 'Sparkling champagne (750ml)'],
                ];
                break;

            case 'Cocktails':
                $items = [
                    ['name' => 'Mojito', 'price' => 12000, 'description' => 'Rum, mint, lime, soda'],
                    ['name' => 'Margarita', 'price' => 13000, 'description' => 'Tequila, triple sec, lime'],
                    ['name' => 'Piña Colada', 'price' => 14000, 'description' => 'Rum, coconut cream, pineapple'],
                    ['name' => 'Cosmopolitan', 'price' => 13000, 'description' => 'Vodka, cranberry, lime'],
                    ['name' => 'Long Island Iced Tea', 'price' => 15000, 'description' => 'Five spirits mixed cocktail'],
                    ['name' => 'Bloody Mary', 'price' => 12000, 'description' => 'Vodka, tomato juice, spices'],
                ];
                break;

            case 'Soft Drinks':
                $items = [
                    ['name' => 'Coca Cola', 'price' => 2000, 'description' => 'Canned soft drink (330ml)'],
                    ['name' => 'Sprite', 'price' => 2000, 'description' => 'Lemon-lime soda (330ml)'],
                    ['name' => 'Fanta Orange', 'price' => 2000, 'description' => 'Orange flavored soda (330ml)'],
                    ['name' => 'Tonic Water', 'price' => 2500, 'description' => 'Premium tonic water (300ml)'],
                    ['name' => 'Fresh Juice', 'price' => 4000, 'description' => 'Freshly squeezed juice (250ml)'],
                    ['name' => 'Mineral Water', 'price' => 1500, 'description' => 'Still or sparkling (500ml)'],
                ];
                break;

            case 'Bar Snacks':
                $items = [
                    ['name' => 'Mixed Nuts', 'price' => 5000, 'description' => 'Roasted cashews, almonds, peanuts'],
                    ['name' => 'Potato Chips', 'price' => 3000, 'description' => 'Crispy potato chips with dip'],
                    ['name' => 'Nachos with Salsa', 'price' => 8000, 'description' => 'Corn chips with salsa and cheese'],
                    ['name' => 'Chicken Wings', 'price' => 12000, 'description' => '6 pieces with hot or BBQ sauce'],
                    ['name' => 'Mini Burgers', 'price' => 15000, 'description' => '3 slider burgers with fries'],
                ];
                break;
        }

        foreach ($items as $itemData) {
            MenuItem::firstOrCreate([
                'outlet_id' => $outlet->id,
                'name' => $itemData['name'],
            ], [
                'id' => Str::uuid(),
                'category_id' => $category->id,
                'price' => $itemData['price'],
                'cost_price' => $itemData['price'] * 0.4, // 40% cost
                'description' => $itemData['description'] ?? null,
                'status' => 'active',
                'is_available' => true,
            ]);
        }
    }
}
