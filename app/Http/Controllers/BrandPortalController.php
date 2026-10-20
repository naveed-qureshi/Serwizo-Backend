<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\BrandLedger;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BrandPortalController extends Controller
{
    protected function getAuthenticatedBrand(): Brand
    {
        $user = Auth::user();
        if (!$user || !$user->brand_id) {
            abort(403, 'Unauthorized. No brand associated with this user account.');
        }

        return $user->brand;
    }

    public function dashboard(Request $request)
    {
        $brand = $this->getAuthenticatedBrand();

        $totalJobs = ServiceRequest::where('brand_id', $brand->id)->count();
        $completedJobs = ServiceRequest::where('brand_id', $brand->id)->whereIn('status', ['completed', 'verified'])->count();
        $pendingJobs = ServiceRequest::where('brand_id', $brand->id)->whereIn('status', ['new', 'pending_assignment', 'assigned', 'accepted', 'in_progress'])->count();
        
        $totalBilled = BrandLedger::where('brand_id', $brand->id)->where('transaction_type', 'job_billing')->where('is_reversed', false)->sum('debit');
        $totalPaid = BrandLedger::where('brand_id', $brand->id)->where('transaction_type', 'payment_received')->where('is_reversed', false)->sum('credit');
        $currentBalance = $brand->getLedgerBalance();

        $recentJobs = ServiceRequest::where('brand_id', $brand->id)
            ->with(['customer', 'city', 'proofs', 'currentWorker'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('brand_portal.dashboard', compact(
            'brand',
            'totalJobs',
            'completedJobs',
            'pendingJobs',
            'totalBilled',
            'totalPaid',
            'currentBalance',
            'recentJobs'
        ));
    }

    public function jobs(Request $request)
    {
        $brand = $this->getAuthenticatedBrand();

        $query = ServiceRequest::where('brand_id', $brand->id)
            ->with(['customer', 'city', 'serviceJob', 'currentWorker', 'proofs']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sr_number', 'like', "%{$search}%")
                  ->orWhere('model_number', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate(DB::raw('COALESCE(completed_at, created_at)'), '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate(DB::raw('COALESCE(completed_at, created_at)'), '<=', $request->end_date);
        }

        $jobs = $query->orderByRaw('COALESCE(completed_at, created_at) DESC')->paginate(15)->withQueryString();

        return view('brand_portal.jobs', compact('brand', 'jobs'));
    }

    public function showJob(ServiceRequest $serviceRequest)
    {
        $brand = $this->getAuthenticatedBrand();

        if ($serviceRequest->brand_id !== $brand->id) {
            abort(403, 'Unauthorized access to this job.');
        }

        $serviceRequest->load(['customer', 'city', 'serviceJob', 'currentWorker', 'proofs', 'statusHistory']);

        return view('brand_portal.job_show', compact('brand', 'serviceRequest'));
    }

    public function ledger(Request $request)
    {
        $brand = $this->getAuthenticatedBrand();
        $currentBalance = $brand->getLedgerBalance();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();

        // Opening Balance before startDate (Sum of debit - credit before startDate based on effective completion date)
        $openingDebit = BrandLedger::leftJoin('service_requests', 'service_requests.id', '=', 'brand_ledgers.service_request_id')
            ->where('brand_ledgers.brand_id', $brand->id)
            ->where('brand_ledgers.is_reversed', false)
            ->where(DB::raw('COALESCE(service_requests.completed_at, brand_ledgers.created_at)'), '<', $startDate)
            ->sum('brand_ledgers.debit');

        $openingCredit = BrandLedger::leftJoin('service_requests', 'service_requests.id', '=', 'brand_ledgers.service_request_id')
            ->where('brand_ledgers.brand_id', $brand->id)
            ->where('brand_ledgers.is_reversed', false)
            ->where(DB::raw('COALESCE(service_requests.completed_at, brand_ledgers.created_at)'), '<', $startDate)
            ->sum('brand_ledgers.credit');

        $openingBalance = (float)($openingDebit - $openingCredit);

        $transactions = BrandLedger::with(['serviceRequest.customer', 'serviceRequest.city', 'serviceRequest.serviceJob'])
            ->leftJoin('service_requests', 'service_requests.id', '=', 'brand_ledgers.service_request_id')
            ->where('brand_ledgers.brand_id', $brand->id)
            ->whereBetween(DB::raw('COALESCE(service_requests.completed_at, brand_ledgers.created_at)'), [$startDate, $endDate])
            ->select('brand_ledgers.*')
            ->orderByRaw('COALESCE(service_requests.completed_at, brand_ledgers.created_at) ASC, brand_ledgers.id ASC')
            ->get();

        $running = $openingBalance;
        foreach ($transactions as $tx) {
            if (!$tx->is_reversed) {
                $running = $running + (float)$tx->debit - (float)$tx->credit;
            }
            $tx->statement_balance = $running;
        }

        $totalDebit = $transactions->where('is_reversed', false)->sum('debit');
        $totalCredit = $transactions->where('is_reversed', false)->sum('credit');

        return view('brand_portal.ledger', compact(
            'brand',
            'currentBalance',
            'openingBalance',
            'transactions',
            'totalDebit',
            'totalCredit',
            'startDate',
            'endDate'
        ));
    }

    public function exportExcel(Request $request)
    {
        $brand = $this->getAuthenticatedBrand();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();

        // Opening Balance before startDate (Sum of debit - credit before startDate based on effective completion date)
        $openingDebit = BrandLedger::leftJoin('service_requests', 'service_requests.id', '=', 'brand_ledgers.service_request_id')
            ->where('brand_ledgers.brand_id', $brand->id)
            ->where('brand_ledgers.is_reversed', false)
            ->where(DB::raw('COALESCE(service_requests.completed_at, brand_ledgers.created_at)'), '<', $startDate)
            ->sum('brand_ledgers.debit');

        $openingCredit = BrandLedger::leftJoin('service_requests', 'service_requests.id', '=', 'brand_ledgers.service_request_id')
            ->where('brand_ledgers.brand_id', $brand->id)
            ->where('brand_ledgers.is_reversed', false)
            ->where(DB::raw('COALESCE(service_requests.completed_at, brand_ledgers.created_at)'), '<', $startDate)
            ->sum('brand_ledgers.credit');

        $openingBalance = (float)($openingDebit - $openingCredit);

        $transactions = BrandLedger::with(['serviceRequest.customer', 'serviceRequest.city', 'serviceRequest.serviceJob'])
            ->leftJoin('service_requests', 'service_requests.id', '=', 'brand_ledgers.service_request_id')
            ->where('brand_ledgers.brand_id', $brand->id)
            ->whereBetween(DB::raw('COALESCE(service_requests.completed_at, brand_ledgers.created_at)'), [$startDate, $endDate])
            ->select('brand_ledgers.*')
            ->orderByRaw('COALESCE(service_requests.completed_at, brand_ledgers.created_at) ASC, brand_ledgers.id ASC')
            ->get();

        $running = $openingBalance;
        foreach ($transactions as $tx) {
            if (!$tx->is_reversed) {
                $running = $running + (float)$tx->debit - (float)$tx->credit;
            }
            $tx->statement_balance = $running;
        }

        $brandNameClean = preg_replace('/[^A-Za-z0-9_\-]/', '_', $brand->name);
        $fileName = "Brand_Ledger_{$brandNameClean}_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Assigned At',
            'Completed At',
            'Duration',
            'Job number',
            'Customer',
            'Contact',
            'Description',
            'City',
            'Debit',
            'Credit',
            'Balance'
        ];

        $callback = function () use ($transactions, $columns, $openingBalance) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns);

            // Opening balance row
            fputcsv($file, [
                '-',
                '-',
                '-',
                '-',
                'OPENING BALANCE BROUGHT FORWARD',
                '-',
                'Balance Brought Forward',
                '-',
                '-',
                '-',
                number_format($openingBalance, 2)
            ]);

            foreach ($transactions as $tx) {
                $sr = $tx->serviceRequest;
                $hasJob = (bool)$sr;
                $assignedTime = $hasJob ? ($sr->assigned_at ?? $sr->started_at ?? $sr->created_at) : $tx->created_at;
                $completedTime = $hasJob ? ($sr->completed_at ?? $sr->verified_at) : null;
                $duration = $hasJob ? $sr->formatted_duration : '-';
                $jobNo = $hasJob ? $sr->sr_number : ($tx->reference_number ?: $tx->transaction_number);
                $customer = $hasJob ? ($sr->customer->name ?? '-') : 'Corporate Settlement';
                $contact = $hasJob ? ($sr->customer->phone ?? '-') : '-';
                
                $desc = '';
                if ($hasJob) {
                    $desc = $sr->serviceJob?->title ?? 'Installation Service';
                    if ($sr->model_number || $sr->serial_number) {
                        $descParts = [];
                        if ($sr->model_number) $descParts[] = "Model: " . $sr->model_number;
                        if ($sr->serial_number) $descParts[] = "S/N: " . $sr->serial_number;
                        $desc .= " (" . implode(', ', $descParts) . ")";
                    }
                } else {
                    $desc = $tx->description;
                }

                $city = $hasJob ? ($sr->city?->name ?? $sr->customer?->city?->name ?? '-') : '-';

                fputcsv($file, [
                    $assignedTime ? $assignedTime->format('d M Y h:i A') : '-',
                    $completedTime ? $completedTime->format('d M Y h:i A') : '-',
                    $duration,
                    $jobNo,
                    $customer,
                    $contact,
                    $desc,
                    $city,
                    $tx->debit > 0 ? number_format($tx->debit, 2) : '-',
                    $tx->credit > 0 ? number_format($tx->credit, 2) : '-',
                    number_format($tx->statement_balance ?? $tx->running_balance, 2)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
