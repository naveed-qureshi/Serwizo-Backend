<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'worker_id',
        'assigned_by_user_id',
        'status',
        'rejection_reason',
        'assigned_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'responded_at' => 'datetime',
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

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }
}
