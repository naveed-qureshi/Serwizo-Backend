<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'icon',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function serviceJobs()
    {
        return $this->hasMany(ServiceJob::class);
    }
}
