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
        Schema::create('staffs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->enum('payment_mode', ['salary', 'hourly', 'commission'])->default('commission')->nullable();
            $table->enum('commission_type', ['fixed', 'percentage'])->default('fixed')->nullable();
            $table->string('amount')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};
