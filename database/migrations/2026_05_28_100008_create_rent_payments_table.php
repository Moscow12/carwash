<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_payments', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('tenancy_agreement_id', 36);
            $table->date('payment_date');
            $table->decimal('amount_paid', 12, 2);
            $table->char('payment_method_id', 36)->nullable();
            $table->string('reference_no', 100)->nullable()->comment('M-Pesa TxID, cheque no, etc.');
            $table->date('payment_for_month')->comment('First day of the month this payment covers');
            $table->char('received_by', 36)->nullable()->comment('users.id');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenancy_agreement_id')->references('id')->on('tenancy_agreements')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('set null');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['tenancy_agreement_id', 'payment_for_month']);
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_payments');
    }
};
