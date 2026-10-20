<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->boolean('is_free_installation')->default(false)->after('service_job_id');
            $table->boolean('is_brand_job')->default(false)->after('is_free_installation');
            $table->foreignId('brand_id')->nullable()->after('is_brand_job')->constrained('brands')->nullOnDelete();
            
            $table->string('model_number', 100)->nullable()->after('brand_id');
            $table->string('serial_number', 100)->nullable()->after('model_number');
            $table->integer('quantity')->default(1)->after('serial_number');
            $table->decimal('brand_rate', 12, 2)->default(0.00)->after('customer_price'); // Amount receivable from Brand
            
            $table->timestamp('started_at')->nullable()->after('assigned_at');
            $table->integer('duration_minutes')->nullable()->after('completed_at');
            $table->boolean('is_brand_ledger_posted')->default(false)->after('is_ledger_posted');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn([
                'is_free_installation',
                'is_brand_job',
                'brand_id',
                'model_number',
                'serial_number',
                'quantity',
                'brand_rate',
                'started_at',
                'duration_minutes',
                'is_brand_ledger_posted',
            ]);
        });
    }
};
