<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenancy_agreements', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('customer_id', 36)->comment('Tenant — references customers.id');
            $table->char('landlord_id', 36);
            $table->char('property_id', 36);
            $table->char('rental_unit_id', 36);

            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('rent_amount', 12, 2);
            $table->decimal('deposit_paid', 12, 2)->default(0);
            $table->enum('payment_frequency', ['monthly', 'quarterly', 'semi_annual', 'annual'])->default('monthly');
            $table->enum('agreement_status', ['draft', 'active', 'terminated', 'expired', 'renewed'])->default('draft');
            $table->text('notes')->nullable();

            $table->char('created_by', 36)->nullable()->comment('users.id of staff who created');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
            $table->foreign('landlord_id')->references('id')->on('landlords')->onDelete('restrict');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('restrict');
            $table->foreign('rental_unit_id')->references('id')->on('rental_units')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['agreement_status', 'start_date']);
            $table->index(['rental_unit_id', 'agreement_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenancy_agreements');
    }
};
