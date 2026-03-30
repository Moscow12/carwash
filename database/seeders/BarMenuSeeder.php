<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\MenuCategory;
use App\Models\MenuItem;

class BarMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find first business
        $business = Business::where('type', 'hotel')->first();

        if (!$business) {
            $this->command->error('No hotel business found. Please create a business first.');
            return;
        }

        // Find or create a bar outlet
        $outlet = PosOutlet::where('business_id', $business->id)
            ->where('type', 'bar')
            ->first();

        if (!$outlet) {
            $outlet = PosOutlet::create([
                'business_id' => $business->id,
                'name' => 'Main Bar',
                'type' => 'bar',
                'location' => 'Ground Floor',
                'status' => 'active',
            ]);
        }

        $this->command->info("Using outlet: {$outlet->name}");

        // Create Menu Categories with display order
        $categories = [
            ['name' => 'Beer', 'description' => 'Local and imported beers', 'display_order' => 1],
            ['name' => 'Spirits', 'description' => 'Whisky, Vodka, Gin, Rum, Tequila', 'display_order' => 2],
            ['name' => 'Wine', 'description' => 'Red, White, and Rosé wines', 'display_order' => 3],
            ['name' => 'Cocktails', 'description' => 'Classic and signature cocktails', 'display_order' => 4],
            ['name' => 'Soft Drinks', 'description' => 'Non-alcoholic beverages', 'display_order' => 5],
            ['name' => 'Snacks', 'description' => 'Bar snacks and light bites', 'display_order' => 6],
        ];

        $categoryIds = [];
        foreach ($categories as $cat) {
            $category = MenuCategory::updateOrCreate(
                [
                    'outlet_id' => $outlet->id,
                    'name' => $cat['name']
                ],
                [
                    'description' => $cat['description'],
                    'display_order' => $cat['display_order'],
                    'status' => 'active',
                ]
            );
            $categoryIds[$cat['name']] = $category->id;
        }

        $this->command->info('Categories created successfully.');

        // Create Menu Items with realistic Tanzanian prices (TSh)
        $menuItems = [
            // BEER (TSh 3,000 - 8,000)
            ['category' => 'Beer', 'name' => 'Safari Lager', 'price' => 3500, 'cost' => 2500, 'description' => 'Local premium lager beer'],
            ['category' => 'Beer', 'name' => 'Kilimanjaro Premium', 'price' => 4000, 'cost' => 2800, 'description' => 'Tanzania\'s finest lager'],
            ['category' => 'Beer', 'name' => 'Serengeti Premium', 'price' => 4000, 'cost' => 2800, 'description' => 'Premium local beer'],
            ['category' => 'Beer', 'name' => 'Tusker', 'price' => 4500, 'cost' => 3200, 'description' => 'Kenyan premium lager'],
            ['category' => 'Beer', 'name' => 'Heineken', 'price' => 6000, 'cost' => 4200, 'description' => 'Dutch premium lager'],
            ['category' => 'Beer', 'name' => 'Corona', 'price' => 7000, 'cost' => 5000, 'description' => 'Mexican beer with lime'],
            ['category' => 'Beer', 'name' => 'Guinness', 'price' => 5500, 'cost' => 3900, 'description' => 'Irish stout beer'],

            // SPIRITS (TSh 8,000 - 25,000 per shot/drink)
            ['category' => 'Spirits', 'name' => 'Konyagi (Shot)', 'price' => 3000, 'cost' => 1800, 'description' => 'Local Tanzanian gin'],
            ['category' => 'Spirits', 'name' => 'Smirnoff Vodka (Shot)', 'price' => 5000, 'cost' => 3000, 'description' => 'Premium vodka'],
            ['category' => 'Spirits', 'name' => 'Absolut Vodka (Shot)', 'price' => 7000, 'cost' => 4500, 'description' => 'Swedish premium vodka'],
            ['category' => 'Spirits', 'name' => 'Johnnie Walker Red (Shot)', 'price' => 8000, 'cost' => 5000, 'description' => 'Blended Scotch whisky'],
            ['category' => 'Spirits', 'name' => 'Johnnie Walker Black (Shot)', 'price' => 12000, 'cost' => 7500, 'description' => '12 year old Scotch'],
            ['category' => 'Spirits', 'name' => 'Bacardi White Rum (Shot)', 'price' => 6000, 'cost' => 3800, 'description' => 'White rum'],
            ['category' => 'Spirits', 'name' => 'Captain Morgan Spiced (Shot)', 'price' => 6500, 'cost' => 4000, 'description' => 'Spiced rum'],
            ['category' => 'Spirits', 'name' => 'Beefeater Gin (Shot)', 'price' => 7000, 'cost' => 4500, 'description' => 'London dry gin'],
            ['category' => 'Spirits', 'name' => 'Bombay Sapphire (Shot)', 'price' => 9000, 'cost' => 5800, 'description' => 'Premium gin'],
            ['category' => 'Spirits', 'name' => 'Jose Cuervo Tequila (Shot)', 'price' => 8000, 'cost' => 5200, 'description' => 'Silver tequila'],

            // WINE (TSh 35,000 - 80,000 per bottle)
            ['category' => 'Wine', 'name' => 'House Red Wine (Glass)', 'price' => 8000, 'cost' => 4000, 'description' => 'South African red'],
            ['category' => 'Wine', 'name' => 'House White Wine (Glass)', 'price' => 8000, 'cost' => 4000, 'description' => 'South African white'],
            ['category' => 'Wine', 'name' => 'Nederburg Cabernet (Bottle)', 'price' => 45000, 'cost' => 28000, 'description' => 'South African red wine'],
            ['category' => 'Wine', 'name' => 'KWV Chardonnay (Bottle)', 'price' => 40000, 'cost' => 25000, 'description' => 'South African white'],
            ['category' => 'Wine', 'name' => 'Rosé Wine (Glass)', 'price' => 9000, 'cost' => 4500, 'description' => 'Pink wine'],

            // COCKTAILS (TSh 10,000 - 18,000)
            ['category' => 'Cocktails', 'name' => 'Mojito', 'price' => 12000, 'cost' => 5000, 'description' => 'Rum, mint, lime, soda'],
            ['category' => 'Cocktails', 'name' => 'Margarita', 'price' => 13000, 'cost' => 5500, 'description' => 'Tequila, lime, triple sec'],
            ['category' => 'Cocktails', 'name' => 'Piña Colada', 'price' => 14000, 'cost' => 6000, 'description' => 'Rum, coconut, pineapple'],
            ['category' => 'Cocktails', 'name' => 'Cosmopolitan', 'price' => 13500, 'cost' => 5800, 'description' => 'Vodka, cranberry, lime'],
            ['category' => 'Cocktails', 'name' => 'Long Island Iced Tea', 'price' => 15000, 'cost' => 6500, 'description' => 'Five spirits with cola'],
            ['category' => 'Cocktails', 'name' => 'Daiquiri', 'price' => 11000, 'cost' => 4800, 'description' => 'Rum, lime, sugar'],
            ['category' => 'Cocktails', 'name' => 'Mai Tai', 'price' => 14000, 'cost' => 6000, 'description' => 'Rum, orange, almond syrup'],
            ['category' => 'Cocktails', 'name' => 'Whisky Sour', 'price' => 13000, 'cost' => 5500, 'description' => 'Whisky, lemon, sugar'],
            ['category' => 'Cocktails', 'name' => 'Gin & Tonic', 'price' => 10000, 'cost' => 4500, 'description' => 'Gin with tonic water'],
            ['category' => 'Cocktails', 'name' => 'Bloody Mary', 'price' => 12000, 'cost' => 5200, 'description' => 'Vodka, tomato juice, spices'],

            // SOFT DRINKS (TSh 2,000 - 4,000)
            ['category' => 'Soft Drinks', 'name' => 'Coca Cola', 'price' => 2500, 'cost' => 1200, 'description' => '330ml can'],
            ['category' => 'Soft Drinks', 'name' => 'Pepsi', 'price' => 2500, 'cost' => 1200, 'description' => '330ml can'],
            ['category' => 'Soft Drinks', 'name' => 'Sprite', 'price' => 2500, 'cost' => 1200, 'description' => '330ml can'],
            ['category' => 'Soft Drinks', 'name' => 'Fanta Orange', 'price' => 2500, 'cost' => 1200, 'description' => '330ml can'],
            ['category' => 'Soft Drinks', 'name' => 'Stoney Tangawizi', 'price' => 3000, 'cost' => 1500, 'description' => 'Ginger beer'],
            ['category' => 'Soft Drinks', 'name' => 'Fresh Orange Juice', 'price' => 4000, 'cost' => 1800, 'description' => 'Freshly squeezed'],
            ['category' => 'Soft Drinks', 'name' => 'Mineral Water', 'price' => 2000, 'cost' => 800, 'description' => '500ml bottle'],
            ['category' => 'Soft Drinks', 'name' => 'Red Bull', 'price' => 5000, 'cost' => 2800, 'description' => 'Energy drink 250ml'],
            ['category' => 'Soft Drinks', 'name' => 'Tonic Water', 'price' => 2500, 'cost' => 1200, 'description' => '330ml can'],

            // SNACKS (TSh 4,000 - 15,000)
            ['category' => 'Snacks', 'name' => 'Peanuts', 'price' => 4000, 'cost' => 1500, 'description' => 'Roasted salted peanuts'],
            ['category' => 'Snacks', 'name' => 'Crisps', 'price' => 3500, 'cost' => 1300, 'description' => 'Potato chips'],
            ['category' => 'Snacks', 'name' => 'Samosas (4 pcs)', 'price' => 6000, 'cost' => 2500, 'description' => 'Beef or vegetable'],
            ['category' => 'Snacks', 'name' => 'Chicken Wings (6 pcs)', 'price' => 12000, 'cost' => 5500, 'description' => 'Spicy or BBQ'],
            ['category' => 'Snacks', 'name' => 'Spring Rolls (4 pcs)', 'price' => 7000, 'cost' => 3000, 'description' => 'Vegetable spring rolls'],
            ['category' => 'Snacks', 'name' => 'Mixed Nuts', 'price' => 5000, 'cost' => 2000, 'description' => 'Cashews, almonds, peanuts'],
            ['category' => 'Snacks', 'name' => 'Chips Masala', 'price' => 8000, 'cost' => 3500, 'description' => 'French fries with spices'],
            ['category' => 'Snacks', 'name' => 'Beef Kebab (3 sticks)', 'price' => 10000, 'cost' => 4500, 'description' => 'Grilled beef skewers'],
        ];

        foreach ($menuItems as $item) {
            MenuItem::updateOrCreate(
                [
                    'outlet_id' => $outlet->id,
                    'name' => $item['name'],
                ],
                [
                    'category_id' => $categoryIds[$item['category']],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'cost_price' => $item['cost'],
                    'status' => 'active',
                    'is_available' => true,
                    'prep_time_mins' => in_array($item['category'], ['Cocktails', 'Snacks']) ? 5 : 2,
                ]
            );
        }

        $this->command->info('Menu items created successfully with realistic Tanzanian prices!');
        $this->command->info('Total items: ' . count($menuItems));
    }
}
