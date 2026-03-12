<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hotel_tax_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->string('name', 80)->comment('e.g. VAT 18%, Service Charge 10%');
            $table->enum('type', ['VAT', 'service_charge', 'levy', 'tourism', 'other'])->default('VAT');
            $table->decimal('rate', 5, 2);
            $table->enum('applies_to', ['rooms', 'food', 'beverages', 'all'])->default('all');
            $table->boolean('is_inclusive')->default(false)->comment('1 = tax is already included in price');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_tax_configs');
    }
};
