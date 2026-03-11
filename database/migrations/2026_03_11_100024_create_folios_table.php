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
        Schema::create('folios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('folio_no', 20)->unique()->comment('e.g. FOL-2026-00789');
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->foreignUuid('reservation_id')->nullable()->constrained('reservations')->onDelete('set null');
            $table->foreignUuid('guest_id')->nullable()->constrained('guests')->onDelete('set null');
            $table->enum('status', ['open', 'closed', 'settled'])->default('open');
            $table->decimal('total_charges', 14, 2)->default(0.00);
            $table->decimal('total_payments', 14, 2)->default(0.00);
            $table->decimal('balance', 14, 2)->default(0.00)->comment('total_charges minus total_payments');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folios');
    }
};
