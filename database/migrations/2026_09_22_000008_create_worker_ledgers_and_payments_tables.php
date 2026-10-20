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
        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 40)->unique(); // TX-2026-00001
            $table->foreignId('worker_id')->constrained('workers')->restrictOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->nullOnDelete();
            $table->string('transaction_type', 30); 
            // job_earning, payment, advance, bonus, deduction, adjustment, refund, reversal
            $table->decimal('debit', 12, 2)->default(0.00); // Outflow / Paid to worker / Penalty
            $table->decimal('credit', 12, 2)->default(0.00); // Inflow / Worker earning / Bonus
            $table->decimal('running_balance', 12, 2)->default(0.00); // Cumulative balance (Credit - Debit)
            $table->string('description', 255);
            $table->string('reference_number', 100)->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_reversed')->default(false);
            $table->foreignId('reversed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reversal_transaction_id')->nullable()->constrained('ledger_transactions')->nullOnDelete();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();

            $table->index(['worker_id', 'created_at']);
            $table->index('transaction_type');
        });

        Schema::create('worker_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 40)->unique(); // PAY-2026-00001
            $table->foreignId('worker_id')->constrained('workers')->restrictOnDelete();
            $table->foreignId('ledger_transaction_id')->nullable()->constrained('ledger_transactions')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50)->default('bank_transfer'); // cash, bank_transfer, jazzcash, easypaisa, other
            $table->string('reference_number', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_payments');
        Schema::dropIfExists('ledger_transactions');
    }
};
