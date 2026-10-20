<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'province',
        'status',
    ];

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function workers()
    {
        return $this->hasMany(Worker::class, 'primary_city_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function jobPrices()
    {
        return $this->hasMany(ServiceJobPrice::class);
    }
}
