<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'worker_id',
        'service_request_id',
        'transaction_type',
        'debit',
        'credit',
        'running_balance',
        'description',
        'reference_number',
        'created_by_user_id',
        'is_reversed',
        'reversed_by_user_id',
        'reversal_transaction_id',
        'reversal_reason',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'running_balance' => 'decimal:2',
            'is_reversed' => 'boolean',
        ];
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function reversedBy()
    {
        return $this->belongsTo(User::class, 'reversed_by_user_id');
    }

    public function reversalTransaction()
    {
        return $this->belongsTo(LedgerTransaction::class, 'reversal_transaction_id');
    }

    public function payment()
    {
        return $this->hasOne(WorkerPayment::class);
    }
}
