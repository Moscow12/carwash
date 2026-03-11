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
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_no', 20)->unique()->comment('e.g. ORD-2026-00456');
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->foreignUuid('outlet_id')->constrained('pos_outlets')->onDelete('restrict');
            $table->foreignUuid('session_id')->nullable()->constrained('pos_sessions')->onDelete('set null');
            $table->foreignUuid('table_id')->nullable()->constrained('pos_tables')->onDelete('set null');
            $table->foreignUuid('reservation_id')->nullable()->constrained('reservations')->onDelete('set null')->comment('Set when order is charged to a room');
            $table->foreignUuid('guest_id')->nullable()->constrained('guests')->onDelete('set null');
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->onDelete('set null')->comment('Walk-in retail customer');
            $table->enum('order_type', ['dine_in', 'takeaway', 'room_service', 'bar', 'delivery'])->default('dine_in');
            $table->tinyInteger('covers')->default(1);
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('service_charge', 10, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->enum('status', ['open', 'sent_to_kitchen', 'ready', 'served', 'billed', 'paid', 'voided'])->default('open');
            $table->text('notes')->nullable();
            $table->foreignUuid('served_by')->nullable()->constrained('staffs')->onDelete('set null')->comment('FK staffs.id - waiter');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_orders');
    }
};
