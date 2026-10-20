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
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->boolean('is_brand_job')->default(false)->after('status');
            $table->foreignId('brand_id')->nullable()->after('is_brand_job')->constrained('brands')->nullOnDelete();
            $table->boolean('is_free_installation')->default(false)->after('brand_id');
            $table->decimal('brand_rate', 12, 2)->default(0.00)->after('is_free_installation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn(['is_brand_job', 'brand_id', 'is_free_installation', 'brand_rate']);
        });
    }
};
