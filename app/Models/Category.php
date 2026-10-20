<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'icon',
        'status',
    ];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function serviceJobs()
    {
        return $this->hasMany(ServiceJob::class);
    }

    public function workers()
    {
        return $this->belongsToMany(Worker::class, 'worker_categories');
    }

    public function cities()
    {
        return $this->belongsToMany(City::class, 'category_cities')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function isAvailableInCity(?int $cityId): bool
    {
        if (!$cityId) {
            return true;
        }
        // If no specific cities are attached, it is available everywhere by default
        if ($this->cities()->count() === 0) {
            return true;
        }
        return $this->cities()->where('cities.id', $cityId)->wherePivot('is_active', true)->exists();
    }

    public function scopeForCity($query, ?int $cityId)
    {
        if (!$cityId) {
            return $query;
        }
        return $query->where(function ($q) use ($cityId) {
            $q->whereDoesntHave('cities')
              ->orWhereHas('cities', function ($sub) use ($cityId) {
                  $sub->where('cities.id', $cityId)->where('category_cities.is_active', true);
              });
        });
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
