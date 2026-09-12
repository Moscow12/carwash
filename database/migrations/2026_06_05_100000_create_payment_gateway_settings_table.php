<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_settings', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->enum('active_environment', ['test', 'live'])->default('test');

            $table->text('test_consumer_key')->nullable();
            $table->text('test_consumer_secret')->nullable();
            $table->string('test_ipn_id')->nullable();

            $table->text('live_consumer_key')->nullable();
            $table->text('live_consumer_secret')->nullable();
            $table->string('live_ipn_id')->nullable();

            $table->enum('ipn_notification_type', ['GET', 'POST'])->default('GET');

            $table->timestamp('last_test_connection_at')->nullable();
            $table->boolean('last_test_connection_ok')->nullable();
            $table->text('last_test_connection_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_settings');
    }
};
