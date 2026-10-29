<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\BrandLedger;
use App\Services\BrandLedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BrandLedgerController extends Controller
{
    public function __construct(
        protected BrandLedgerService $brandLedgerService
    ) {}

    public function index(Request $request)
    {
        $brands = Brand::withCount('serviceRequests')->orderBy('name')->get();

        $selectedBrand = null;
        $transactions = collect();
        $openingBalance = 0.00;
        $currentBalance = 0.00;
        $totalDebit = 0.00;
        $totalCredit = 0.00;

        $brandId = $request->get('brand_id', $brands->first()?->id);

        if ($brandId) {
            $this->brandLedgerService->recalculateBrandBalances((int)$brandId);
            $selectedBrand = Brand::findOrFail($brandId);
            $currentBalance = $selectedBrand->getLedgerBalance();

            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();

            // Opening Balance before startDate (Sum of debit - credit before startDate based on effective completion date)
            $openingDebit = BrandLedger::leftJoin('service_requests', 'service_requests.id', '=', 'brand_ledgers.service_request_id')
                ->where('brand_ledgers.brand_id', $brandId)
                ->where('brand_ledgers.is_reversed', false)
                ->where(DB::raw('COALESCE(service_requests.completed_at, brand_ledgers.created_at)'), '<', $startDate)
                ->sum('brand_ledgers.debit');

            $openingCredit = BrandLedger::leftJoin('service_requests', 'service_requests.id', '=', 'brand_ledgers.service_request_id')
                ->where('brand_ledgers.brand_id', $brandId)
                ->where('brand_ledgers.is_reversed', false)
                ->where(DB::raw('COALESCE(service_requests.completed_at, brand_ledgers.created_at)'), '<', $startDate)
                ->sum('brand_ledgers.credit');

            $openingBalance = (float)($openingDebit - $openingCredit);

            // Transactions in range based on Job Completion Date
            $transactions = BrandLedger::with(['serviceRequest.customer', 'serviceRequest.city', 'serviceRequest.serviceJob', 'createdBy', 'reversedBy'])
                ->leftJoin('service_requests', 'service_requests.id', '=', 'brand_ledgers.service_request_id')
                ->where('brand_ledgers.brand_id', $brandId)
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
        }

        return view('brand_ledger.index', compact(
            'brands',
            'selectedBrand',
            'transactions',
            'openingBalance',
            'currentBalance',
            'totalDebit',
            'totalCredit'
        ));
    }

    public function storePayment(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,cheque,online,other',
            'reference_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->brandLedgerService->postPayment(
                brand: $brand,
                amount: (float)$validated['amount'],
                paymentMethod: $validated['payment_method'],
                paymentDate: $validated['payment_date'],
                refNo: $validated['reference_number'] ?? null,
                bankName: $validated['bank_name'] ?? null,
                notes: $validated['notes'] ?? null,
                userId: Auth::id()
            );

            return back()->with('success', "Payment of Rs. " . number_format($validated['amount'], 2) . " successfully recorded for {$brand->name}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeAdjustment(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|in:debit_adjustment,credit_adjustment',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:100',
        ]);

        try {
            $this->brandLedgerService->postManualTransaction(
                brand: $brand,
                transactionType: $validated['transaction_type'],
                amount: (float)$validated['amount'],
                description: $validated['description'],
                refNo: $validated['reference_number'] ?? null,
                userId: Auth::id()
            );

            return back()->with('success', "Adjustment of Rs. " . number_format($validated['amount'], 2) . " posted successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reverse(Request $request, BrandLedger $brandLedger)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:255',
        ]);

        try {
            $this->brandLedgerService->reverseTransaction(
                transaction: $brandLedger,
                reason: $validated['reason'],
                userId: Auth::id()
            );

            return back()->with('success', "Transaction {$brandLedger->transaction_number} reversed successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function exportExcel(Request $request, Brand $brand)
    {
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

        // Transactions in range
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
