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
        Schema::create('guests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->onDelete('set null')->comment('FK customers.id - set if guest is also a retail customer');
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('email', 200)->nullable();
            $table->string('phone', 25)->nullable();
            $table->char('nationality', 2)->nullable()->comment('ISO 3166-1 alpha-2 e.g. TZ, KE, US');
            $table->enum('id_type', ['passport', 'nida', 'driving_license', 'voter_id'])->nullable();
            $table->string('id_number', 60)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['M', 'F', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->enum('vip_level', ['standard', 'silver', 'gold', 'platinum'])->default('standard');
            $table->integer('loyalty_points')->default(0);
            $table->json('preferences')->nullable()->comment('Pillow type, floor preference, dietary notes, etc.');
            $table->boolean('blacklisted')->default(false);
            $table->text('blacklist_reason')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('email');
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
