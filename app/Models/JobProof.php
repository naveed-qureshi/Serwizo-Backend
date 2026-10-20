<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobProof extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'service_request_id',
        'worker_id',
        'proof_type',
        'image_path',
        'caption',
        'latitude',
        'longitude',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'created_at' => 'datetime',
        ];
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function getImageUrlAttribute(): string
    {
        return route('proofs.media', $this->id);
    }
}
