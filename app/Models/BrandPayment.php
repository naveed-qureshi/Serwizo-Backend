<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'brand_id',
        'brand_ledger_id',
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

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function ledgerTransaction()
    {
        return $this->belongsTo(BrandLedger::class, 'brand_ledger_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
