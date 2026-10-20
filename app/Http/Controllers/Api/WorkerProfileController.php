<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkerProfileController extends Controller
{
    public function profile(Request $request)
    {
        $worker = $request->user()->load(['primaryCity', 'categories', 'workerCategories.subCategory', 'bankDetail']);

        $totalJobs = $worker->serviceRequests()->count();
        $completedJobs = $worker->serviceRequests()->whereIn('status', ['completed', 'verified'])->count();
        $assignedJobs = $worker->serviceRequests()->whereIn('status', ['assigned', 'accepted'])->count();
        $inProgressJobs = $worker->serviceRequests()->whereIn('status', ['in_progress', 'on_the_way', 'arrived'])->count();
        $currentBalance = $worker->getLedgerBalance();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $worker->worker_code,
                'database_id' => $worker->id,
                'worker_code' => $worker->worker_code,
                'name' => $worker->name,
                'mobile' => $worker->phone,
                'phone' => $worker->phone,
                'email' => $worker->email,
                'city' => $worker->primaryCity?->name ?? 'Lahore',
                'primary_city' => $worker->primaryCity?->name ?? 'Lahore',
                'skill_level' => $worker->skill_level,
                'experience_years' => $worker->experience_years,
                'status' => strtoupper($worker->status),
                'availability' => strtoupper($worker->availability ?? 'AVAILABLE'),
                'photo_url' => $worker->photo_url,
                'skills' => $worker->categories->pluck('title')->all() ?: ['General Maintenance'],
                'cnic_verified' => true,
                'reference_verified' => true,
                'technical_evaluation_passed' => true,
                'terms_accepted' => true,
                'metrics' => [
                    'total_jobs' => $totalJobs,
                    'completed_jobs' => $completedJobs,
                    'assigned_jobs' => $assignedJobs,
                    'in_progress_jobs' => $inProgressJobs,
                    'current_ledger_balance' => (float)$currentBalance,
                ],
                'bank_details' => [
                    'bank_name' => $worker->bankDetail?->bank_name,
                    'account_title' => $worker->bankDetail?->account_title,
                    'account_number' => $worker->bankDetail?->account_number,
                    'iban' => $worker->bankDetail?->iban,
                    'verification_status' => $worker->bankDetail?->verification_status ?? 'not_verified',
                ],
            ],
        ]);
    }

    public function updateAvailability(Request $request)
    {
        $worker = $request->user();

        $validated = $request->validate([
            'availability' => ['required', Rule::in(['available', 'busy', 'on_leave', 'unavailable'])],
        ]);

        $worker->update(['availability' => $validated['availability']]);

        return response()->json([
            'success' => true,
            'message' => 'Availability updated successfully.',
            'data' => [
                'availability' => $worker->availability,
            ],
        ]);
    }
}
