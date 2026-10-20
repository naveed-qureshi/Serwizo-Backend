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
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('worker_code', 30)->unique(); // e.g. SW-W-1001
            $table->string('name', 150);
            $table->string('cnic_number', 30)->unique();
            $table->string('phone', 30)->unique();
            $table->string('email', 150)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('primary_city_id')->constrained('cities')->restrictOnDelete();
            $table->string('primary_category_id')->nullable();
            $table->string('primary_sub_category_id')->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->string('skill_level', 30)->default('intermediate'); // basic, intermediate, experienced, expert
            $table->string('status', 20)->default('active'); // active, inactive, suspended
            $table->string('availability', 20)->default('available'); // available, busy, on_leave, unavailable
            $table->string('password');
            $table->string('device_token', 255)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('worker_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['worker_id', 'category_id', 'sub_category_id'], 'worker_cat_sub_unique');
        });

        Schema::create('worker_service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['worker_id', 'city_id', 'area_id'], 'worker_city_area_unique');
        });

        Schema::create('worker_bank_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_title', 150)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('iban', 50)->nullable();
            $table->string('verification_status', 30)->default('not_verified'); // not_verified, verified
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('worker_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('phone', 30);
            $table->string('relationship', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('worker_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->string('document_type', 50); // photo, cnic_front, cnic_back, certificate, other
            $table->string('file_path', 255);
            $table->string('file_name', 255)->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->unsignedInteger('file_size_bytes')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_documents');
        Schema::dropIfExists('worker_references');
        Schema::dropIfExists('worker_bank_details');
        Schema::dropIfExists('worker_service_areas');
        Schema::dropIfExists('worker_categories');
        Schema::dropIfExists('workers');
    }
};
