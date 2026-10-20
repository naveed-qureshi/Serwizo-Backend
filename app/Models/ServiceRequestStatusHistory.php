<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestStatusHistory extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'service_request_status_history';

    protected $fillable = [
        'service_request_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'worker_id',
        'notes',
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

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
