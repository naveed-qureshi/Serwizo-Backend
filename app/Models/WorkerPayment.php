<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'worker_id',
        'ledger_transaction_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'bank_name',
        'notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function ledgerTransaction()
    {
        return $this->belongsTo(LedgerTransaction::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
