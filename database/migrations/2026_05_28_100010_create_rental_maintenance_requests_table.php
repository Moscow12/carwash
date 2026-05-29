<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_maintenance_requests', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('tenancy_agreement_id', 36);
            $table->enum('maintenance_type', [
                'plumbing', 'electrical', 'painting', 'roofing', 'furniture',
                'appliance', 'pest_control', 'cleaning', 'security', 'other'
            ]);
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed', 'cancelled'])->default('open');
            $table->char('assigned_to', 36)->nullable()->comment('staffs.id');
            $table->timestamps();

            $table->foreign('tenancy_agreement_id')->references('id')->on('tenancy_agreements')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('staffs')->onDelete('set null');

            $table->index(['status', 'maintenance_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_maintenance_requests');
    }
};
