<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Customer;
use App\Models\LedgerTransaction;
use App\Models\ServiceRequest;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->query('range', 'today');
        $customStart = $request->query('start_date');
        $customEnd = $request->query('end_date');

        [$startDate, $endDate] = match ($range) {
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'custom' => [
                $customStart ? Carbon::parse($customStart)->startOfDay() : Carbon::today()->startOfDay(),
                $customEnd ? Carbon::parse($customEnd)->endOfDay() : Carbon::today()->endOfDay()
            ],
            default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()], // 'today'
        };

        // Worker Metrics
        $totalWorkers = Worker::count();
        $activeWorkers = Worker::where('status', 'active')->count();
        $availableWorkers = Worker::where('status', 'active')->where('availability', 'available')->count();
        $totalCustomers = Customer::count();

        // Operational Pipeline within date range
        $periodRequests = ServiceRequest::whereBetween('created_at', [$startDate, $endDate]);
        $totalPeriodRequests = (clone $periodRequests)->count();
        $pendingRequests = (clone $periodRequests)->whereIn('status', ['new', 'pending_assignment'])->count();
        $assignedRequests = (clone $periodRequests)->where('status', 'assigned')->count();
        $inProgressRequests = (clone $periodRequests)->whereIn('status', ['accepted', 'on_the_way', 'arrived', 'in_progress'])->count();
        $completedPeriodRequests = (clone $periodRequests)->whereIn('status', ['completed', 'verified'])->count();
        $cancelledRequests = (clone $periodRequests)->where('status', 'cancelled')->count();

        // Financial Metrics for the period (Job Completion Date)
        $completedJobsInPeriod = ServiceRequest::whereBetween(DB::raw('COALESCE(completed_at, created_at)'), [$startDate, $endDate])
            ->whereIn('status', ['completed', 'verified'])
            ->get();

        $periodRevenue = $completedJobsInPeriod->sum('customer_price');
        $periodWorkerPayable = $completedJobsInPeriod->sum('worker_payment');
        $periodCommission = $completedJobsInPeriod->sum('commission_amount');

        // Overall outstanding ledger payable across all workers
        // In our running balance calculation: positive balance = payable to workers
        $latestTxIds = LedgerTransaction::select(DB::raw('MAX(id) as id'))
            ->groupBy('worker_id')
            ->pluck('id');

        $totalOutstandingPayable = LedgerTransaction::whereIn('id', $latestTxIds)
            ->where('running_balance', '>', 0)
            ->sum('running_balance');

        // Recent Activity Lists
        $recentRequests = ServiceRequest::with(['customer', 'serviceJob', 'city', 'currentWorker'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentWorkers = Worker::with(['primaryCity', 'categories'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentCompletedJobs = ServiceRequest::with(['customer', 'serviceJob', 'currentWorker'])
            ->whereIn('status', ['completed', 'verified'])
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get();

        // Chart Data (filtered by selected date range)
        $statusCounts = ServiceRequest::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $categoryCounts = Category::withCount(['serviceRequests' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get()
            ->filter(fn($c) => $c->service_requests_count > 0 || true)
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->title, 'count' => $c->service_requests_count])
            ->values();

        $cityCounts = City::withCount(['serviceRequests' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'count' => $c->service_requests_count])
            ->values();

        return view('dashboard.index', compact(
            'range',
            'startDate',
            'endDate',
            'totalWorkers',
            'activeWorkers',
            'availableWorkers',
            'totalCustomers',
            'totalPeriodRequests',
            'pendingRequests',
            'assignedRequests',
            'inProgressRequests',
            'completedPeriodRequests',
            'cancelledRequests',
            'periodRevenue',
            'periodWorkerPayable',
            'periodCommission',
            'totalOutstandingPayable',
            'recentRequests',
            'recentWorkers',
            'recentCompletedJobs',
            'statusCounts',
            'categoryCounts',
            'cityCounts'
        ));
    }
}
