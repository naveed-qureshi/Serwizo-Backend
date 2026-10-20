<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LedgerTransaction;
use Illuminate\Http\Request;

class WorkerLedgerController extends Controller
{
    public function ledger(Request $request)
    {
        $worker = $request->user();

        $transactions = LedgerTransaction::with('serviceRequest')
            ->where('worker_id', $worker->id)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        $totalEarnings = LedgerTransaction::where('worker_id', $worker->id)
            ->where('transaction_type', 'job_earning')
            ->where('is_reversed', false)
            ->sum('credit');

        $totalPayments = LedgerTransaction::where('worker_id', $worker->id)
            ->where('transaction_type', 'payment')
            ->where('is_reversed', false)
            ->sum('debit');

        $currentBalance = $worker->getLedgerBalance();

        $txList = $transactions->map(fn($tx) => [
            'id' => (string)$tx->id,
            'transaction_number' => $tx->transaction_number,
            'type' => $tx->transaction_type,
            'description' => $tx->description,
            'debit' => (float)$tx->debit,
            'credit' => (float)$tx->credit,
            'amount' => (float)($tx->credit > 0 ? $tx->credit : -$tx->debit),
            'running_balance' => (float)$tx->running_balance,
            'is_reversed' => (bool)$tx->is_reversed,
            'date' => $tx->created_at->format('d M Y, h:i A'),
            'created_at' => $tx->created_at->toIso8601String(),
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'current_balance' => (float)$currentBalance,
                    'balance_status' => $currentBalance >= 0 ? 'payable_to_you' : 'advance_receivable',
                    'total_earnings' => (float)$totalEarnings,
                    'total_payments' => (float)$totalPayments,
                ],
                'transactions' => $txList,
            ],
        ]);
    }
}
