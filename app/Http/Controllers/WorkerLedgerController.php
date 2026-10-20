<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\LedgerTransaction;
use App\Models\Worker;
use App\Services\WorkerLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkerLedgerController extends Controller
{
    public function __construct(
        protected WorkerLedgerService $ledgerService
    ) {}

    public function index(Request $request)
    {
        $query = Worker::with(['primaryCity', 'bankDetail']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('worker_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($cityId = $request->query('city_id')) {
            $query->where('primary_city_id', $cityId);
        }

        $workers = $query->paginate(15)->withQueryString();

        // Calculate ledger totals across all workers
        $latestTxIds = LedgerTransaction::select(DB::raw('MAX(id) as id'))
            ->groupBy('worker_id')
            ->pluck('id');

        $totalPayable = LedgerTransaction::whereIn('id', $latestTxIds)
            ->where('running_balance', '>', 0)
            ->sum('running_balance');

        $totalReceivable = LedgerTransaction::whereIn('id', $latestTxIds)
            ->where('running_balance', '<', 0)
            ->sum('running_balance');

        $cities = City::where('status', 'active')->get();

        return view('ledger.index', compact('workers', 'totalPayable', 'totalReceivable', 'cities'));
    }

    public function show(Request $request, Worker $worker)
    {
        $worker->load(['primaryCity', 'bankDetail']);

        $query = LedgerTransaction::with([
            'serviceRequest.customer',
            'serviceRequest.serviceJob',
            'serviceRequest.category',
            'createdBy',
            'reversedBy'
        ])
        ->leftJoin('service_requests', 'service_requests.id', '=', 'ledger_transactions.service_request_id')
        ->where('ledger_transactions.worker_id', $worker->id)
        ->select('ledger_transactions.*');

        if ($startDate = $request->query('start_date')) {
            $query->whereDate(DB::raw('COALESCE(service_requests.completed_at, ledger_transactions.created_at)'), '>=', $startDate);
        }

        if ($endDate = $request->query('end_date')) {
            $query->whereDate(DB::raw('COALESCE(service_requests.completed_at, ledger_transactions.created_at)'), '<=', $endDate);
        }

        if ($type = $request->query('type')) {
            $query->where('ledger_transactions.transaction_type', $type);
        }

        $transactions = $query->orderByRaw('COALESCE(service_requests.completed_at, ledger_transactions.created_at) DESC, ledger_transactions.id DESC')->paginate(20)->withQueryString();

        $totalEarnings = LedgerTransaction::where('worker_id', $worker->id)
            ->where('transaction_type', 'job_earning')
            ->where('is_reversed', false)
            ->sum('credit');

        $totalPayments = LedgerTransaction::where('worker_id', $worker->id)
            ->where('transaction_type', 'payment')
            ->where('is_reversed', false)
            ->sum('debit');

        $currentBalance = $worker->getLedgerBalance();

        return view('ledger.show', compact('worker', 'transactions', 'totalEarnings', 'totalPayments', 'currentBalance'));
    }

    public function postManualTransaction(Request $request, Worker $worker)
    {
        $validated = $request->validate([
            'transaction_type' => ['required', Rule::in(['bonus', 'deduction', 'adjustment', 'advance', 'refund'])],
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['required', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        $this->ledgerService->postManualTransaction(
            $worker,
            $validated['transaction_type'],
            $validated['amount'],
            $validated['description'],
            $validated['reference_number'] ?? null,
            Auth::id()
        );

        return back()->with('success', 'Ledger transaction posted successfully.');
    }

    public function reverseTransaction(Request $request, LedgerTransaction $transaction)
    {
        $validated = $request->validate([
            'reversal_reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->ledgerService->reverseTransaction($transaction, $validated['reversal_reason'], Auth::id());
            return back()->with('success', 'Transaction reversed successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function exportCsv(Request $request, Worker $worker)
    {
        $query = LedgerTransaction::with([
            'serviceRequest.customer',
            'serviceRequest.serviceJob',
            'serviceRequest.category',
        ])
        ->leftJoin('service_requests', 'service_requests.id', '=', 'ledger_transactions.service_request_id')
        ->where('ledger_transactions.worker_id', $worker->id)
        ->select('ledger_transactions.*');

        if ($startDate = $request->query('start_date')) {
            $query->whereDate(DB::raw('COALESCE(service_requests.completed_at, ledger_transactions.created_at)'), '>=', $startDate);
        }

        if ($endDate = $request->query('end_date')) {
            $query->whereDate(DB::raw('COALESCE(service_requests.completed_at, ledger_transactions.created_at)'), '<=', $endDate);
        }

        if ($type = $request->query('type')) {
            $query->where('ledger_transactions.transaction_type', $type);
        }

        $transactions = $query->orderByRaw('COALESCE(service_requests.completed_at, ledger_transactions.created_at) ASC, ledger_transactions.id ASC')->get();

        $fileName = "Worker_Ledger_{$worker->worker_code}_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Date & Time',
            'SR Number',
            'Customer Name',
            'Job Title / Description',
            'Debit',
            'Credit',
            'Balance',
        ];

        $callback = function () use ($transactions, $columns, $worker) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($transactions as $tx) {
                $sr = $tx->serviceRequest;
                $srNumber = $sr ? $sr->sr_number : ($tx->reference_number ?: '-');
                $customerName = $sr && $sr->customer ? $sr->customer->name : '-';
                
                if ($sr) {
                    $jobTitle = $sr->serviceJob?->title ?? $sr->category?->name ?? 'Service Request';
                    if ($sr->model_number) {
                        $jobTitle .= " (Model: " . $sr->model_number . ")";
                    }
                    $description = $jobTitle;
                    if ($tx->description && $tx->description !== 'Job completed earning') {
                        $description .= " - " . $tx->description;
                    }
                } else {
                    $description = strtoupper(str_replace('_', ' ', $tx->transaction_type)) . ($tx->description ? ": {$tx->description}" : '');
                }

                fputcsv($file, [
                    $tx->created_at->format('d M Y h:i A'),
                    $srNumber,
                    $customerName,
                    $description,
                    $tx->debit > 0 ? number_format($tx->debit, 2, '.', '') : '0.00',
                    $tx->credit > 0 ? number_format($tx->credit, 2, '.', '') : '0.00',
                    number_format($tx->running_balance, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
