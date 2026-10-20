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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('sr_number', 40)->unique(); // e.g. SR-260901-0001
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('city_id')->constrained('cities')->restrictOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->foreignId('service_job_id')->constrained('service_jobs')->restrictOnDelete();
            $table->text('service_address');
            $table->string('location_notes', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('customer_complaint')->nullable();
            $table->text('special_instructions')->nullable();
            $table->date('preferred_date')->nullable();
            $table->string('preferred_time', 50)->nullable();
            $table->string('request_source', 30)->default('phone'); // phone, whatsapp, website, brand, worker, other
            
            // Workflow status
            $table->string('status', 30)->default('new'); 
            // new, pending_assignment, assigned, accepted, on_the_way, arrived, in_progress, completed, verified, cancelled

            $table->foreignId('current_worker_id')->nullable()->constrained('workers')->nullOnDelete();
            
            // Financial figures for this job
            $table->decimal('customer_price', 12, 2)->default(0.00);
            $table->decimal('worker_payment', 12, 2)->default(0.00);
            $table->decimal('commission_amount', 12, 2)->default(0.00);

            $table->boolean('is_ledger_posted')->default(false);
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'city_id']);
            $table->index('preferred_date');
        });

        Schema::create('service_request_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('worker_id')->nullable()->constrained('workers')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('worker_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending'); // pending, accepted, rejected, cancelled, completed
            $table->text('rejection_reason')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('worker_assignment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('previous_worker_id')->nullable()->constrained('workers')->nullOnDelete();
            $table->foreignId('new_worker_id')->constrained('workers')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('job_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->string('proof_type', 50)->default('completion'); // before_work, after_work, installation, completion, other
            $table->string('image_path', 255);
            $table->string('caption', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_proofs');
        Schema::dropIfExists('worker_assignment_history');
        Schema::dropIfExists('worker_assignments');
        Schema::dropIfExists('service_request_status_history');
        Schema::dropIfExists('service_requests');
    }
};
