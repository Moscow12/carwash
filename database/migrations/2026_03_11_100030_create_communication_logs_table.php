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
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->enum('channel', ['sms', 'email', 'whatsapp', 'push'])->default('sms');
            $table->string('recipient', 200)->comment('Phone number or email address');
            $table->foreignUuid('guest_id')->nullable()->constrained('guests')->onDelete('set null');
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('template', 80)->nullable()->comment('e.g. reservation_confirm, payment_receipt');
            $table->string('subject', 300)->nullable()->comment('Email subject line');
            $table->text('body');
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending');
            $table->string('provider_ref', 200)->nullable()->comment('SMS/email provider message ID');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
