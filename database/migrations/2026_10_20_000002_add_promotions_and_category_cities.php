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
        // 1. Add promotion pricing fields to service_jobs
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->boolean('is_promotional')->default(false)->after('default_commission');
            $table->decimal('promotional_customer_price', 10, 2)->nullable()->after('is_promotional');
            $table->decimal('promotional_worker_payment', 10, 2)->nullable()->after('promotional_customer_price');
            $table->decimal('promotional_commission', 10, 2)->nullable()->after('promotional_worker_payment');
        });

        // 2. Create category_cities pivot table for city-wise category activation
        Schema::create('category_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category_id', 'city_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_cities');

        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'is_promotional',
                'promotional_customer_price',
                'promotional_worker_payment',
                'promotional_commission',
            ]);
        });
    }
};
