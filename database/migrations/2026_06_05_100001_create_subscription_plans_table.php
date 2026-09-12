<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('name');
            $table->string('business_type')->nullable(); // null = applies to any business type
            $table->decimal('fee_amount', 12, 2);
            $table->char('currency', 3)->default('TZS');
            $table->enum('billing_interval', ['monthly', 'yearly'])->default('monthly');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
