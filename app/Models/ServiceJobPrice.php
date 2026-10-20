<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceJobPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_job_id',
        'city_id',
        'customer_price',
        'worker_payment',
        'commission',
    ];

    protected function casts(): array
    {
        return [
            'customer_price' => 'decimal:2',
            'worker_payment' => 'decimal:2',
            'commission' => 'decimal:2',
        ];
    }

    public function serviceJob()
    {
        return $this->belongsTo(ServiceJob::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
