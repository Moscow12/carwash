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
        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->foreignUuid('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignUuid('assigned_to')->nullable()->constrained('staffs')->onDelete('set null')->comment('FK staffs.id');
            $table->enum('task_type', ['checkout', 'stayover', 'deep_clean', 'turndown', 'inspection'])->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'in_progress', 'done', 'verified'])->default('pending');
            $table->date('scheduled_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->onDelete('set null')->comment('FK users.id - supervisor');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housekeeping_tasks');
    }
};
