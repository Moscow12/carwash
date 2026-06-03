<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** The fixed catalogue of modules a business can be granted. */
    private array $modules = [
        ['key' => 'pos',        'name' => 'POS / Sales',  'icon' => 'ti-building-warehouse'],
        ['key' => 'rental',     'name' => 'Rental',       'icon' => 'ti-home-2'],
        ['key' => 'hotel',      'name' => 'Hotel',        'icon' => 'ti-bed'],
        ['key' => 'restaurant', 'name' => 'Restaurant',   'icon' => 'ti-tools-kitchen-2'],
        ['key' => 'bar',        'name' => 'Bar',          'icon' => 'ti-glass-full'],
        ['key' => 'carwash',    'name' => 'Carwash',      'icon' => 'ti-car'],
    ];

    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('key')->unique();   // machine name: pos, rental, …
            $table->string('name');            // display label
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('business_modules', function (Blueprint $table) {
            $table->char('business_id', 36);
            $table->char('module_id', 36);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->primary(['business_id', 'module_id']);
            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
            $table->foreign('module_id')->references('id')->on('modules')->cascadeOnDelete();
        });

        // Seed the module catalogue.
        foreach ($this->modules as $m) {
            DB::table('modules')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'key' => $m['key'],
                'name' => $m['name'],
                'icon' => $m['icon'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('business_modules');
        Schema::dropIfExists('modules');
    }
};
