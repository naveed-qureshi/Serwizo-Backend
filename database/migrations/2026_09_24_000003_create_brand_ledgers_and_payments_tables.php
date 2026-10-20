<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 40)->unique(); // BL-2026-00001
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->nullOnDelete();
            $table->string('transaction_type', 30); // job_billing, payment_received, adjustment, reversal
            
            // Debit = Amount Billed / Receivable from Brand
            // Credit = Payment Received from Brand
            $table->decimal('debit', 12, 2)->default(0.00);
            $table->decimal('credit', 12, 2)->default(0.00);
            $table->decimal('running_balance', 12, 2)->default(0.00); // Net receivable from brand
            
            $table->string('description', 255);
            $table->string('reference_number', 100)->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->boolean('is_reversed')->default(false);
            $table->foreignId('reversed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reversal_transaction_id')->nullable()->constrained('brand_ledgers')->nullOnDelete();
            $table->string('reversal_reason', 255)->nullable();
            
            $table->timestamps();

            $table->index(['brand_id', 'created_at']);
        });

        Schema::create('brand_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 40)->unique(); // BPAY-2026-00001
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->foreignId('brand_ledger_id')->constrained('brand_ledgers')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50); // bank_transfer, cheque, online, cash, other
            $table->string('reference_number', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_payments');
        Schema::dropIfExists('brand_ledgers');
    }
};
