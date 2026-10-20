<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\WorkerPayment;
use App\Services\WorkerLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class WorkerPaymentController extends Controller
{
    public function __construct(
        protected WorkerLedgerService $ledgerService
    ) {}

    public function index(Request $request)
    {
        $query = WorkerPayment::with(['worker.primaryCity', 'createdBy']);

        if ($workerId = $request->query('worker_id')) {
            $query->where('worker_id', $workerId);
        }

        if ($method = $request->query('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($startDate = $request->query('start_date')) {
            $query->whereDate('payment_date', '>=', $startDate);
        }

        if ($endDate = $request->query('end_date')) {
            $query->whereDate('payment_date', '<=', $endDate);
        }

        $payments = $query->orderBy('payment_date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $workers = Worker::where('status', 'active')->get();

        return view('payments.index', compact('payments', 'workers'));
    }

    public function create(Request $request)
    {
        $selectedWorkerId = $request->query('worker_id');
        $workers = Worker::with('bankDetail')->where('status', 'active')->get()->map(function ($w) {
            $w->ledger_balance = $w->getLedgerBalance();
            return $w;
        });

        $workersMap = [];
        foreach ($workers as $w) {
            $workersMap[(string)$w->id] = [
                'id' => $w->id,
                'name' => $w->name,
                'code' => $w->worker_code,
                'balance' => (float)$w->ledger_balance,
            ];
        }

        return view('payments.create', compact('workers', 'workersMap', 'selectedWorkerId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => ['required', 'exists:workers,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'jazzcash', 'easypaisa', 'other'])],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $worker = Worker::findOrFail($validated['worker_id']);
        $currentBalance = (float)$worker->getLedgerBalance();

        if ($currentBalance <= 0) {
            return back()->withInput()->withErrors(['amount' => "Worker {$worker->name} has no payable balance to disburse (Current Balance: Rs. " . number_format($currentBalance, 2) . ")."]);
        }

        if ((float)$validated['amount'] > $currentBalance) {
            return back()->withInput()->withErrors(['amount' => "Disbursement amount (Rs. " . number_format((float)$validated['amount'], 2) . ") cannot be more than the worker's total balance of Rs. " . number_format($currentBalance, 2) . "."]);
        }

        try {
            $payment = $this->ledgerService->postPayment(
                $worker,
                (float)$validated['amount'],
                $validated['payment_method'],
                $validated['payment_date'],
                $validated['reference_number'] ?? null,
                $validated['bank_name'] ?? null,
                $validated['notes'] ?? null,
                Auth::id()
            );

            return redirect()->route('payments.index')->with('success', "Payment of Rs. " . number_format($payment->amount, 2) . " posted to {$worker->name} successfully.");
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }
    }
}
