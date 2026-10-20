<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'brand_id',
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

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function reversedBy()
    {
        return $this->belongsTo(User::class, 'reversed_by_user_id');
    }

    public function reversalTransaction()
    {
        return $this->belongsTo(BrandLedger::class, 'reversal_transaction_id');
    }
}
