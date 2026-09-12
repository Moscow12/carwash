<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_subscriptions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36)->unique();
            $table->char('plan_id', 36)->nullable();
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('subscription_plans')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_subscriptions');
    }
};
