<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\LedgerTransaction;
use App\Models\BrandLedger;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestStatusHistory;
use App\Models\Worker;
use App\Models\WorkerAssignment;
use App\Models\WorkerAssignmentHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class ServiceRequestService
{
    public function __construct(
        protected WorkerLedgerService $workerLedgerService,
        protected BrandLedgerService $brandLedgerService
    ) {}

    public function generateSrNumber(): string
    {
        $year = date('y'); // e.g. '26'
        $prefix = 'SR-' . $year;
        
        // Find highest sequence number for this year's prefix
        $lastSr = ServiceRequest::withTrashed()
            ->where('sr_number', 'LIKE', "{$prefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($lastSr && preg_match('/^SR-\d{2}-(\d+)$/', $lastSr->sr_number, $matches)) {
            $nextSeq = ((int)$matches[1]) + 1;
        } else {
            $yearCount = ServiceRequest::withTrashed()
                ->whereYear('created_at', Carbon::now()->year)
                ->count();
            $nextSeq = $yearCount + 1;
        }

        return sprintf('%s-%06d', $prefix, $nextSeq);
    }

    /**
     * Get suitable workers for a given service request.
     */
    public function getSuitableWorkers(ServiceRequest $serviceRequest)
    {
        $cityId = $serviceRequest->city_id;
        $categoryId = $serviceRequest->category_id;
        $subCategoryId = $serviceRequest->sub_category_id;

        return Worker::with(['primaryCity', 'categories'])
            ->where('status', 'active')
            ->where(function ($query) use ($cityId) {
                $query->where('primary_city_id', $cityId)
                    ->orWhereHas('serviceAreas', function ($q) use ($cityId) {
                        $q->where('city_id', $cityId);
                    });
            })
            ->where(function ($query) use ($categoryId, $subCategoryId) {
                $query->whereHas('workerCategories', function ($q) use ($categoryId, $subCategoryId) {
                    $q->where('category_id', $categoryId);
                    if ($subCategoryId) {
                        $q->where(function ($sq) use ($subCategoryId) {
                            $sq->where('sub_category_id', $subCategoryId)->orWhereNull('sub_category_id');
                        });
                    }
                });
            })
            ->withCount(['serviceRequests as active_jobs_count' => function ($q) {
                $q->whereIn('status', ['assigned', 'accepted', 'on_the_way', 'arrived', 'in_progress']);
            }])
            ->orderByRaw("CASE availability WHEN 'available' THEN 1 WHEN 'busy' THEN 2 WHEN 'on_leave' THEN 3 ELSE 4 END")
            ->orderBy('active_jobs_count', 'asc')
            ->get();
    }

    /**
     * Assign worker to service request.
     */
    public function assignWorker(ServiceRequest $serviceRequest, Worker $worker, ?int $userId = null): ServiceRequest
    {
        return DB::transaction(function () use ($serviceRequest, $worker, $userId) {
            $isReassignment = $serviceRequest->current_worker_id && $serviceRequest->current_worker_id !== $worker->id;
            $oldWorkerId = $serviceRequest->current_worker_id;

            if ($isReassignment) {
                WorkerAssignmentHistory::create([
                    'service_request_id' => $serviceRequest->id,
                    'previous_worker_id' => $oldWorkerId,
                    'new_worker_id' => $worker->id,
                    'reason' => 'Reassigned by operations',
                    'changed_by_user_id' => $userId,
                    'created_at' => now(),
                ]);

                // Cancel previous active assignment
                WorkerAssignment::where('service_request_id', $serviceRequest->id)
                    ->where('worker_id', $oldWorkerId)
                    ->whereIn('status', ['pending', 'accepted'])
                    ->update(['status' => 'cancelled']);
            }

            $serviceRequest->update([
                'current_worker_id' => $worker->id,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

            WorkerAssignment::create([
                'service_request_id' => $serviceRequest->id,
                'worker_id' => $worker->id,
                'assigned_by_user_id' => $userId,
                'status' => 'pending',
                'assigned_at' => now(),
            ]);

            $this->logStatusChange(
                $serviceRequest,
                $serviceRequest->getOriginal('status') ?? 'new',
                'assigned',
                $userId,
                null,
                "Assigned to worker {$worker->name} ({$worker->worker_code})"
            );

            // Send Notification to Worker
            AppNotification::create([
                'notifiable_type' => Worker::class,
                'notifiable_id' => $worker->id,
                'title' => 'New Job Assigned',
                'body' => "You have been assigned job {$serviceRequest->sr_number} in {$serviceRequest->city?->name}.",
                'type' => 'job_assigned',
                'data' => [
                    'service_request_id' => $serviceRequest->id,
                    'sr_number' => $serviceRequest->sr_number,
                    'customer_address' => $serviceRequest->service_address,
                    'job_title' => $serviceRequest->serviceJob?->title,
                    'wage' => $serviceRequest->worker_payment,
                ],
            ]);

            AuditLogService::log(
                action: 'assigned',
                module: 'service_requests',
                recordId: $serviceRequest->id,
                description: "Assigned {$serviceRequest->sr_number} to {$worker->name}",
                userId: $userId
            );

            return $serviceRequest->fresh();
        });
    }

    /**
     * Update Status Workflow
     */
    public function updateStatus(
        ServiceRequest $serviceRequest,
        string $newStatus,
        ?int $userId = null,
        ?int $workerId = null,
        ?string $notes = null
    ): ServiceRequest {
        $oldStatus = $serviceRequest->status;
        if ($oldStatus === $newStatus) {
            return $serviceRequest;
        }

        if ($newStatus === 'completed' && !$serviceRequest->current_worker_id) {
            throw new Exception('Cannot mark job as completed: A technician/worker must be assigned first.');
        }

        if ($newStatus === 'verified' && $serviceRequest->proofs()->count() === 0) {
            throw new Exception('Cannot Verify & Approve QA: At least one photographic proof is required before approval.');
        }

        return DB::transaction(function () use ($serviceRequest, $oldStatus, $newStatus, $userId, $workerId, $notes) {
            $updateData = ['status' => $newStatus];

            if ($newStatus === 'in_progress' && !$serviceRequest->started_at) {
                $updateData['started_at'] = now();
            }

            if ($newStatus === 'completed') {
                $completedAt = now();
                $updateData['completed_at'] = $completedAt;
                
                $startedAt = $serviceRequest->assigned_at ?? $serviceRequest->started_at ?? $serviceRequest->created_at;
                if ($startedAt) {
                    $updateData['duration_minutes'] = (int)Carbon::parse($startedAt)->diffInMinutes($completedAt);
                }
            } elseif ($newStatus === 'verified') {
                $updateData['verified_at'] = now();
                $updateData['verified_by_user_id'] = $userId;
                
                if (!$serviceRequest->completed_at) {
                    $updateData['completed_at'] = now();
                }
            } elseif ($newStatus === 'cancelled' && $notes) {
                $updateData['cancellation_reason'] = $notes;
            }

            $serviceRequest->update($updateData);

            $this->logStatusChange($serviceRequest, $oldStatus, $newStatus, $userId, $workerId, $notes);

            // Post or Reverse Ledger entries
            if (in_array($newStatus, ['completed', 'verified'])) {
                $effectiveBrandId = $serviceRequest->brand_id ?: $serviceRequest->serviceJob?->brand_id;
                
                if ($serviceRequest->is_free_installation && $effectiveBrandId) {
                    $serviceRequest->brand_id = $effectiveBrandId;
                    $serviceRequest->is_brand_job = true;
                    if ((float)$serviceRequest->brand_rate <= 0) {
                        $serviceRequest->brand_rate = (float)($serviceRequest->serviceJob?->brand_rate ?: 2800.00);
                    }
                    $serviceRequest->customer_price = 0.00;
                    $serviceRequest->commission_amount = max(0, (float)$serviceRequest->brand_rate - (float)$serviceRequest->worker_payment);
                    $serviceRequest->save();
                }

                $this->workerLedgerService->syncJobEarning($serviceRequest, $userId);

                if ($effectiveBrandId && (float)$serviceRequest->brand_rate > 0) {
                    $this->brandLedgerService->syncJobBilling($serviceRequest, $userId);
                }
            } else {
                // If status is changed away from completed/verified (e.g. to cancelled, rejected, etc.),
                // any active posted transactions in Worker and Brand ledgers must be reversed automatically!
                $workerTxs = LedgerTransaction::where('service_request_id', $serviceRequest->id)
                    ->where('is_reversed', false)
                    ->where('transaction_type', '!=', 'reversal')
                    ->get();
                foreach ($workerTxs as $tx) {
                    $this->workerLedgerService->reverseTransaction(
                        $tx,
                        "Automatic reversal: Job {$serviceRequest->sr_number} status changed to " . strtoupper(str_replace('_', ' ', $newStatus)) . ($notes ? " ({$notes})" : ''),
                        $userId ?? 1
                    );
                }

                $brandTxs = BrandLedger::where('service_request_id', $serviceRequest->id)
                    ->where('is_reversed', false)
                    ->where('transaction_type', '!=', 'reversal')
                    ->get();
                foreach ($brandTxs as $btx) {
                    $this->brandLedgerService->reverseTransaction(
                        $btx,
                        "Automatic reversal: Job {$serviceRequest->sr_number} status changed to " . strtoupper(str_replace('_', ' ', $newStatus)) . ($notes ? " ({$notes})" : ''),
                        $userId ?? 1
                    );
                }

                if ($serviceRequest->is_ledger_posted || $serviceRequest->is_brand_ledger_posted) {
                    $serviceRequest->update([
                        'is_ledger_posted' => false,
                        'is_brand_ledger_posted' => false,
                    ]);
                }
            }

            // Sync WorkerAssignment status if needed
            if ($serviceRequest->current_worker_id) {
                if ($newStatus === 'accepted') {
                    WorkerAssignment::where('service_request_id', $serviceRequest->id)
                        ->where('worker_id', $serviceRequest->current_worker_id)
                        ->update(['status' => 'accepted', 'responded_at' => now()]);
                } elseif ($newStatus === 'completed' || $newStatus === 'verified') {
                    WorkerAssignment::where('service_request_id', $serviceRequest->id)
                        ->where('worker_id', $serviceRequest->current_worker_id)
                        ->update(['status' => 'completed']);
                }
            }

            AuditLogService::log(
                action: 'status_changed',
                module: 'service_requests',
                recordId: $serviceRequest->id,
                description: "Status changed from {$oldStatus} to {$newStatus} for {$serviceRequest->sr_number}",
                userId: $userId
            );

            return $serviceRequest->fresh();
        });
    }

    /**
     * Delete / Revert Service Request safely (Technical Reversals).
     */
    public function deleteServiceRequest(ServiceRequest $serviceRequest, ?string $reason = null, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($serviceRequest, $reason, $userId) {
            $srNumber = $serviceRequest->sr_number;

            // 1. Reverse Worker Ledger Transactions if posted
            $workerTxs = LedgerTransaction::where('service_request_id', $serviceRequest->id)
                ->where('is_reversed', false)
                ->where('transaction_type', '!=', 'reversal')
                ->get();
            foreach ($workerTxs as $tx) {
                $this->workerLedgerService->reverseTransaction(
                    $tx,
                    "Reversal due to deletion of Service Request {$srNumber}: " . ($reason ?? 'Deleted by Super Admin'),
                    $userId ?? 1
                );
            }

            // 2. Reverse Brand Ledger Transactions if posted
            $brandTxs = BrandLedger::where('service_request_id', $serviceRequest->id)
                ->where('is_reversed', false)
                ->where('transaction_type', '!=', 'reversal')
                ->get();
            foreach ($brandTxs as $btx) {
                $this->brandLedgerService->reverseTransaction(
                    $btx,
                    "Reversal due to deletion of Service Request {$srNumber}: " . ($reason ?? 'Deleted by Super Admin'),
                    $userId ?? 1
                );
            }

            // 3. Cancel active assignments
            WorkerAssignment::where('service_request_id', $serviceRequest->id)->update(['status' => 'cancelled']);

            // 4. Soft delete the service request
            $serviceRequest->delete();

            AuditLogService::log(
                action: 'deleted',
                module: 'service_requests',
                recordId: $serviceRequest->id,
                description: "Deleted service request {$srNumber} with full financial reversal. Reason: " . ($reason ?? 'Super Admin action'),
                userId: $userId
            );

            return true;
        });
    }

    public function logStatusChange(
        ServiceRequest $serviceRequest,
        ?string $fromStatus,
        string $toStatus,
        ?int $userId = null,
        ?int $workerId = null,
        ?string $notes = null
    ): ServiceRequestStatusHistory {
        return ServiceRequestStatusHistory::create([
            'service_request_id' => $serviceRequest->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by_user_id' => $userId,
            'worker_id' => $workerId,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }
}
