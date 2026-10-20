<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code',
        'name',
        'phone',
        'alt_phone',
        'email',
        'city_id',
        'address',
        'notes',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class)->orderBy('created_at', 'desc');
    }

    public function completedJobsCount(): int
    {
        return $this->serviceRequests()->where('status', 'completed')->orWhere('status', 'verified')->count();
    }
}
