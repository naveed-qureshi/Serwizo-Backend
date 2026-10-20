<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'document_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size_bytes',
        'is_verified',
        'verified_by_user_id',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'file_size_bytes' => 'integer',
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
