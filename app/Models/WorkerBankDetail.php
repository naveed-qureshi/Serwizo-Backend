<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerBankDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'bank_name',
        'account_title',
        'account_number',
        'iban',
        'verification_status',
        'verified_by_user_id',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
