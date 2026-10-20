<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\LedgerTransaction;
use App\Models\ServiceJob;
use App\Models\ServiceRequest;
use App\Models\Worker;
use App\Models\WorkerPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function revenue(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $completedJobs = ServiceRequest::with(['city', 'category', 'serviceJob', 'currentWorker'])
            ->whereBetween(DB::raw('COALESCE(completed_at, created_at)'), [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->whereIn('status', ['completed', 'verified'])
            ->get();

        $totalCustomerRevenue = $completedJobs->sum('customer_price');
        $totalWorkerPayout = $completedJobs->sum('worker_payment');
        $totalCommission = $completedJobs->sum('commission_amount');

        // City wise breakdown
        $cityRevenue = $completedJobs->groupBy('city_id')->map(function ($jobs) {
            return [
                'city_name' => $jobs->first()->city?->name ?? 'Unknown',
                'jobs_count' => $jobs->count(),
                'revenue' => $jobs->sum('customer_price'),
                'worker_payout' => $jobs->sum('worker_payment'),
                'commission' => $jobs->sum('commission_amount'),
            ];
        });

        // Category wise breakdown
        $categoryRevenue = $completedJobs->groupBy('category_id')->map(function ($jobs) {
            return [
                'category_name' => $jobs->first()->category?->title ?? 'Unknown',
                'jobs_count' => $jobs->count(),
                'revenue' => $jobs->sum('customer_price'),
                'worker_payout' => $jobs->sum('worker_payment'),
                'commission' => $jobs->sum('commission_amount'),
            ];
        });

        return view('reports.revenue', compact(
            'startDate',
            'endDate',
            'totalCustomerRevenue',
            'totalWorkerPayout',
            'totalCommission',
            'cityRevenue',
            'categoryRevenue',
            'completedJobs'
        ));
    }

    public function jobs(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $query = ServiceRequest::with(['city', 'category', 'serviceJob', 'currentWorker', 'customer'])
            ->whereBetween(DB::raw('COALESCE(completed_at, created_at)'), [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);

        if ($cityId = $request->query('city_id')) {
            $query->where('city_id', $cityId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $jobs = $query->orderByRaw('COALESCE(completed_at, created_at) DESC')->paginate(20)->withQueryString();
        $cities = City::where('status', 'active')->get();

        return view('reports.jobs', compact('jobs', 'cities', 'startDate', 'endDate'));
    }

    public function workers(Request $request)
    {
        $cityId = $request->query('city_id');
        $categoryId = $request->query('category_id');

        $query = Worker::with(['primaryCity', 'categories', 'bankDetail'])
            ->withCount([
                'serviceRequests as total_jobs',
                'serviceRequests as completed_jobs' => fn($q) => $q->whereIn('status', ['completed', 'verified'])
            ]);

        if ($cityId) {
            $query->where('primary_city_id', $cityId);
        }

        if ($categoryId) {
            $query->whereHas('workerCategories', fn($q) => $q->where('category_id', $categoryId));
        }

        $workers = $query->paginate(20)->withQueryString();
        $cities = City::where('status', 'active')->get();
        $categories = Category::where('status', 'active')->get();

        return view('reports.workers', compact('workers', 'cities', 'categories'));
    }

    public function ledger(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        $transactions = LedgerTransaction::with(['worker.primaryCity', 'serviceRequest'])
            ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $totalEarnings = LedgerTransaction::whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->where('transaction_type', 'job_earning')
            ->where('is_reversed', false)
            ->sum('credit');

        $totalPayments = LedgerTransaction::whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->where('transaction_type', 'payment')
            ->where('is_reversed', false)
            ->sum('debit');

        return view('reports.ledger', compact('transactions', 'totalEarnings', 'totalPayments', 'startDate', 'endDate'));
    }
}
