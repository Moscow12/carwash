<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_transactions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('business_subscription_id', 36)->nullable();
            $table->char('plan_id', 36);

            $table->string('merchant_reference', 50)->unique();
            $table->string('order_tracking_id')->nullable()->index();

            $table->decimal('amount', 12, 2);
            $table->char('currency', 3);
            $table->enum('environment', ['test', 'live']);
            $table->enum('status', ['pending', 'submitted', 'completed', 'failed', 'reversed', 'invalid'])->default('pending');

            $table->smallInteger('pesapal_status_code')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('confirmation_code')->nullable();
            $table->json('raw_response')->nullable();
            $table->text('redirect_url')->nullable();

            $table->timestamp('callback_received_at')->nullable();
            $table->timestamp('ipn_received_at')->nullable();

            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete();
            $table->foreign('business_subscription_id')->references('id')->on('business_subscriptions')->nullOnDelete();
            $table->foreign('plan_id')->references('id')->on('subscription_plans')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_transactions');
    }
};
