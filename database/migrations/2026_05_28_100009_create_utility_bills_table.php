<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_bills', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('tenancy_agreement_id', 36);
            $table->enum('bill_type', ['water', 'electricity', 'internet', 'gas', 'security', 'service_charge', 'other']);
            $table->date('billing_month')->comment('First day of the billed month');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['paid', 'unpaid', 'partial', 'waived'])->default('unpaid');
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenancy_agreement_id')->references('id')->on('tenancy_agreements')->onDelete('cascade');

            $table->unique(['tenancy_agreement_id', 'bill_type', 'billing_month'], 'utility_bills_period_unique');
            $table->index(['status', 'billing_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_bills');
    }
};
