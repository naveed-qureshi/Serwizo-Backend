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
        Schema::table('service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('service_requests', 'service_value')) {
                $table->decimal('service_value', 12, 2)->default(0.00)->after('additional_jobs');
            }
            if (!Schema::hasColumn('service_requests', 'parts_value')) {
                $table->decimal('parts_value', 12, 2)->default(0.00)->after('service_value');
            }
            if (!Schema::hasColumn('service_requests', 'job_nature')) {
                $table->string('job_nature', 40)->default('standard')->after('parts_value'); // standard, free_installation, brand_after_sale
            }
            if (!Schema::hasColumn('service_requests', 'warranty_coverage')) {
                $table->string('warranty_coverage', 40)->nullable()->after('job_nature'); // out_of_warranty, in_warranty, partial_warranty
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn([
                'service_value',
                'parts_value',
                'job_nature',
                'warranty_coverage',
            ]);
        });
    }
};
