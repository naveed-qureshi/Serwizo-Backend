<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerAssignmentHistory extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'worker_assignment_history';

    protected $fillable = [
        'service_request_id',
        'previous_worker_id',
        'new_worker_id',
        'reason',
        'changed_by_user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function previousWorker()
    {
        return $this->belongsTo(Worker::class, 'previous_worker_id');
    }

    public function newWorker()
    {
        return $this->belongsTo(Worker::class, 'new_worker_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
