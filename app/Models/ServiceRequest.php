<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sr_number',
        'customer_id',
        'city_id',
        'area_id',
        'category_id',
        'sub_category_id',
        'service_job_id',
        'service_address',
        'location_notes',
        'latitude',
        'longitude',
        'customer_complaint',
        'special_instructions',
        'additional_jobs',
        'service_value',
        'parts_value',
        'job_nature',
        'warranty_coverage',
        'preferred_date',
        'preferred_time',
        'request_source',
        'status',
        'current_worker_id',
        'is_free_installation',
        'is_brand_job',
        'brand_id',
        'model_number',
        'serial_number',
        'quantity',
        'brand_rate',
        'started_at',
        'duration_minutes',
        'is_brand_ledger_posted',
        'customer_price',
        'worker_payment',
        'commission_amount',
        'is_ledger_posted',
        'cancellation_reason',
        'created_by_user_id',
        'assigned_at',
        'completed_at',
        'verified_at',
        'verified_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'customer_price' => 'decimal:2',
            'worker_payment' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'brand_rate' => 'decimal:2',
            'service_value' => 'decimal:2',
            'parts_value' => 'decimal:2',
            'quantity' => 'integer',
            'duration_minutes' => 'integer',
            'is_free_installation' => 'boolean',
            'is_brand_job' => 'boolean',
            'is_ledger_posted' => 'boolean',
            'is_brand_ledger_posted' => 'boolean',
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'verified_at' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'additional_jobs' => 'array',
        ];
    }

    public function getItemsAttribute(): array
    {
        $raw = $this->additional_jobs;
        if (is_string($raw) && !empty($raw)) {
            $raw = json_decode($raw, true);
        }

        if (is_array($raw) && count($raw) > 0) {
            $first = reset($raw);
            if (is_array($first) && (isset($first['title']) || isset($first['job_id']))) {
                $items = [];
                foreach ($raw as $idx => $item) {
                    $item['uid'] = $item['uid'] ?? ('item_' . ($idx + 1) . '_' . ($item['job_id'] ?? 'custom'));
                    $items[] = $item;
                }
                return $items;
            }
        }

        // Fallback for single-job legacy requests
        if ($this->service_job_id || $this->customer_price > 0 || $this->brand_rate > 0) {
            $qty = max(1, (int)($this->quantity ?: 1));
            return [[
                'id' => 1,
                'uid' => 'item_legacy_1',
                'job_id' => $this->service_job_id,
                'title' => $this->serviceJob?->title ?: 'Standard Service',
                'category_id' => $this->category_id,
                'category_title' => $this->category?->title ?: 'General',
                'sub_category_id' => $this->sub_category_id,
                'job_nature' => $this->job_nature ?: ($this->is_free_installation ? 'free_installation' : 'standard'),
                'quantity' => $qty,
                'unit_price' => (float)$this->customer_price / $qty,
                'unit_wage' => (float)$this->worker_payment / $qty,
                'brand_rate' => (float)$this->brand_rate / $qty,
                'total_price' => (float)$this->customer_price,
                'total_wage' => (float)$this->worker_payment,
                'commission' => (float)$this->commission_amount,
            ]];
        }

        return [];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function serviceJob()
    {
        return $this->belongsTo(ServiceJob::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function brandLedgers()
    {
        return $this->hasMany(BrandLedger::class);
    }

    public function currentWorker()
    {
        return $this->belongsTo(Worker::class, 'current_worker_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(ServiceRequestStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function assignments()
    {
        return $this->hasMany(WorkerAssignment::class)->orderBy('created_at', 'desc');
    }

    public function assignmentHistory()
    {
        return $this->hasMany(WorkerAssignmentHistory::class)->orderBy('created_at', 'desc');
    }

    public function proofs()
    {
        return $this->hasMany(JobProof::class)->orderBy('created_at', 'asc');
    }

    public function ledgerTransactions()
    {
        return $this->hasMany(LedgerTransaction::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        $startTime = $this->assigned_at ?? $this->started_at ?? $this->created_at;
        $endTime = $this->completed_at ?? $this->verified_at;

        if ($startTime && $endTime) {
            $start = \Carbon\Carbon::parse($startTime);
            $end = \Carbon\Carbon::parse($endTime);
            if ($end->greaterThanOrEqualTo($start)) {
                $minutes = (int)$start->diffInMinutes($end);
            } else {
                $minutes = (int)($this->duration_minutes ?? 0);
            }
        } else {
            $minutes = (int)($this->duration_minutes ?? 0);
        }

        if ($minutes > 0) {
            $hours = round($minutes / 60, 1);
            if ($hours == 0) {
                $hours = round($minutes / 60, 2);
            }
            $formattedHours = (float)$hours == (int)$hours ? (int)$hours : $hours;
            return $formattedHours . ' ' . ($formattedHours == 1 ? 'hr' : 'hrs');
        }

        return '-';
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'new' => 'bg-blue-100 text-blue-800 border-blue-200',
            'pending_assignment' => 'bg-amber-100 text-amber-800 border-amber-200',
            'assigned' => 'bg-orange-100 text-orange-800 border-orange-200',
            'accepted' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'on_the_way' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
            'arrived' => 'bg-teal-100 text-teal-800 border-teal-200',
            'in_progress' => 'bg-purple-100 text-purple-800 border-purple-200',
            'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'verified' => 'bg-green-100 text-green-800 border-green-200',
            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}
