<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'name',
        'phone',
        'relationship',
        'notes',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
