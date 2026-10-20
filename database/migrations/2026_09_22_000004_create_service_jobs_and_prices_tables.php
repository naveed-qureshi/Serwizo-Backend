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
        Schema::create('service_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->string('title', 200);
            $table->string('code', 50)->unique()->nullable();
            $table->text('description')->nullable();
            $table->integer('estimated_duration_minutes')->default(60);
            $table->decimal('default_customer_price', 12, 2)->default(0.00);
            $table->decimal('default_worker_payment', 12, 2)->default(0.00);
            $table->decimal('default_commission', 12, 2)->default(0.00);
            $table->string('status', 20)->default('active'); // active, inactive
            $table->timestamps();
        });

        Schema::create('service_job_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_job_id')->constrained('service_jobs')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->decimal('customer_price', 12, 2);
            $table->decimal('worker_payment', 12, 2);
            $table->decimal('commission', 12, 2);
            $table->timestamps();

            $table->unique(['service_job_id', 'city_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_job_prices');
        Schema::dropIfExists('service_jobs');
    }
};
