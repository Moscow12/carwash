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
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('pos_outlets')->onDelete('cascade');
            $table->foreignUuid('opened_by')->constrained('users')->onDelete('restrict')->comment('FK users.id');
            $table->decimal('opening_float', 12, 2)->default(0.00);
            $table->decimal('closing_cash', 12, 2)->nullable()->comment('Actual cash counted at close');
            $table->decimal('expected_cash', 12, 2)->nullable()->comment('System-calculated expected cash');
            $table->decimal('variance', 12, 2)->nullable()->comment('closing_cash minus expected_cash');
            $table->foreignUuid('closed_by')->nullable()->constrained('users')->onDelete('set null')->comment('FK users.id');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
