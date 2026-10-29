<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'phone',
        'email',
        'logo_path',
        'default_installation_rate',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'default_installation_rate' => 'decimal:2',
        ];
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function serviceJobs()
    {
        return $this->hasMany(ServiceJob::class)->orderBy('title');
    }

    public function brandLedgerTransactions()
    {
        return $this->hasMany(BrandLedger::class)->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    public function brandPayments()
    {
        return $this->hasMany(BrandPayment::class)->orderBy('payment_date', 'desc');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get current outstanding balance receivable from this Brand.
     */
    public function getLedgerBalance(): float
    {
        $lastTx = BrandLedger::where('brand_id', $this->id)
            ->where('is_reversed', false)
            ->where('transaction_type', '!=', 'reversal')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTx) {
            return (float)$lastTx->running_balance;
        }

        $anyTx = BrandLedger::where('brand_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $anyTx ? (float)$anyTx->running_balance : 0.00;
    }
}
