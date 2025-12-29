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
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_no')->nullable();
            $table->date('expense_date');
            $table->uuid('carwash_id');
            $table->uuid('category_id')->nullable();
            $table->uuid('subcategory_id')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('payment_due', 15, 2)->default(0);
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->string('expense_for')->nullable(); // user/staff reference
            $table->uuid('expense_for_id')->nullable();
            $table->string('contact')->nullable(); // supplier/contact reference
            $table->uuid('contact_id')->nullable();
            $table->text('expense_note')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_interval')->nullable(); // daily, weekly, monthly, yearly
            $table->integer('recurring_count')->nullable();
            $table->date('recurring_end_date')->nullable();
            $table->uuid('added_by')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('carwash_id')->references('id')->on('carwashes')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('expense_categories')->onDelete('set null');
            $table->foreign('subcategory_id')->references('id')->on('expense_categories')->onDelete('set null');
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
