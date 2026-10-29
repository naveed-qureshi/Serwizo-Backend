<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BrandLedgerController;
use App\Http\Controllers\BrandPortalController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceJobController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\WorkerDocumentController;
use App\Http\Controllers\WorkerLedgerController;
use App\Http\Controllers\WorkerPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Direct Media & Storage Serving (Guaranteed cPanel/Shared-Hosting Compatibility)
Route::get('/workers/{worker}/photo', function(\App\Models\Worker $worker) {
    if (!$worker->photo_path) {
        $initials = strtoupper(substr(trim($worker->name), 0, 2)) ?: 'W';
        return response(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="#e2e8f0"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-size="36" font-weight="bold" fill="#475569">' . htmlspecialchars($initials) . '</text></svg>',
            200,
            ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'public, max-age=86400']
        );
    }

    $rawPath = ltrim($worker->photo_path, '/');
    $strippedPath = preg_replace('#^storage/#i', '', $rawPath);

    // 1. Storage disk (public)
    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($strippedPath)) {
        return \Illuminate\Support\Facades\Storage::disk('public')->response($strippedPath, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    // 2. Multi-path directory lookup
    $candidates = [
        storage_path('app/public/' . $strippedPath),
        storage_path('app/' . $strippedPath),
        public_path('storage/' . $strippedPath),
        public_path($strippedPath),
        public_path($rawPath),
        base_path('../public_html/storage/' . $strippedPath),
        base_path('../public_html/' . $strippedPath),
    ];

    foreach ($candidates as $filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            return response()->file($filePath, [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }

    // Fallback: initials SVG avatar if file missing on server disk
    $initials = strtoupper(substr(trim($worker->name), 0, 2)) ?: 'W';
    return response(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="#e2e8f0"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, sans-serif" font-size="36" font-weight="bold" fill="#475569">' . htmlspecialchars($initials) . '</text></svg>',
        200,
        ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'public, max-age=3600']
    );
})->name('workers.photo');

Route::get('/proofs/media/{proof}', function(\App\Models\JobProof $proof) {
    $path = ltrim($proof->image_path, '/');
    $strippedPath = preg_replace('#^storage/#i', '', $path);
    
    // Check in public disk
    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($strippedPath)) {
        return \Illuminate\Support\Facades\Storage::disk('public')->response($strippedPath, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
    
    $candidates = [
        storage_path('app/public/' . $strippedPath),
        storage_path('app/' . $strippedPath),
        public_path('storage/' . $strippedPath),
        public_path($strippedPath),
        public_path($path),
        base_path('../public_html/storage/' . $strippedPath),
        base_path('../public_html/' . $strippedPath),
    ];

    foreach ($candidates as $filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            return response()->file($filePath, [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }
    
    abort(404);
})->name('proofs.media');

// Fallback storage route for any /storage/... asset URL
Route::get('/storage/{path}', function($path) {
    $rawPath = ltrim($path, '/');
    $strippedPath = preg_replace('#^storage/#i', '', $rawPath);

    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($strippedPath)) {
        return \Illuminate\Support\Facades\Storage::disk('public')->response($strippedPath);
    }
    
    $candidates = [
        storage_path('app/public/' . $strippedPath),
        storage_path('app/' . $strippedPath),
        public_path('storage/' . $strippedPath),
        public_path($strippedPath),
        public_path($rawPath),
        base_path('../public_html/storage/' . $strippedPath),
        base_path('../public_html/' . $strippedPath),
    ];

    foreach ($candidates as $filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            return response()->file($filePath);
        }
    }
    
    abort(404);
})->where('path', '.*')->name('storage.fallback');

// Guest authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated session
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    Route::get('/', function() {
        if (auth()->user()->isBrand()) {
            return redirect()->route('brand-portal.dashboard');
        }
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function(Request $request) {
        if (auth()->user()->isBrand()) {
            return redirect()->route('brand-portal.dashboard');
        }
        return app(DashboardController::class)->index($request);
    })->name('dashboard');

    // Brand Portal (for brand user logins)
    Route::middleware('role:brand')->prefix('brand-portal')->name('brand-portal.')->group(function () {
        Route::get('/', [BrandPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/jobs', [BrandPortalController::class, 'jobs'])->name('jobs');
        Route::get('/jobs/{serviceRequest}', [BrandPortalController::class, 'showJob'])->name('jobs.show');
        Route::get('/ledger', [BrandPortalController::class, 'ledger'])->name('ledger');
        Route::get('/ledger/export', [BrandPortalController::class, 'exportExcel'])->name('ledger.export');
    });

    // Operations & Super Admin Routes
    Route::middleware('role:super_admin,operations')->group(function () {
        // Workers
        Route::resource('workers', WorkerController::class);
        Route::patch('/workers/{worker}/status', [WorkerController::class, 'updateStatus'])->name('workers.status');
        Route::post('/workers/{worker}/password', [WorkerController::class, 'quickPasswordUpdate'])->name('workers.password.update');
        Route::get('/workers/documents/{document}', [WorkerDocumentController::class, 'show'])->name('workers.documents.show');
        Route::post('/workers/documents/{document}/verify', [WorkerDocumentController::class, 'verify'])->name('workers.documents.verify');

        // Brands CRUD
        Route::resource('brands', BrandController::class);

        // Categories & Sub-Categories
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('sub-categories', SubCategoryController::class)->except(['show']);
        Route::get('/ajax/categories/{category}/sub-categories', [SubCategoryController::class, 'getByCategory'])->name('ajax.sub-categories');

        // Cities & Areas
        Route::resource('cities', CityController::class)->except(['show', 'destroy']);
        Route::resource('areas', AreaController::class)->except(['show', 'destroy']);
        Route::get('/ajax/cities/{city}/areas', [AreaController::class, 'getByCity'])->name('ajax.areas');

        // Jobs / Services Catalog
        Route::resource('jobs', ServiceJobController::class)->except(['show', 'destroy']);
        Route::get('/ajax/jobs-by-category', [ServiceJobController::class, 'getByCategory'])->name('ajax.jobs-by-category');

        // Customers
        Route::resource('customers', CustomerController::class)->except(['destroy']);
        Route::get('/ajax/customers/search', [CustomerController::class, 'searchByPhone'])->name('ajax.customers.search');

        // Service Requests
        Route::resource('service-requests', ServiceRequestController::class);
        Route::post('/service-requests/{service_request}/assign', [ServiceRequestController::class, 'assignWorker'])->name('service-requests.assign');
        Route::post('/service-requests/{service_request}/reassign', [ServiceRequestController::class, 'reassignWorker'])->name('service-requests.reassign');
        Route::patch('/service-requests/{service_request}/status', [ServiceRequestController::class, 'updateStatus'])->name('service-requests.status');
        Route::post('/service-requests/{service_request}/proofs', [ServiceRequestController::class, 'uploadProof'])->name('service-requests.proofs.upload');
        Route::delete('/proofs/{proof}', [ServiceRequestController::class, 'deleteProof'])->name('service-requests.proofs.delete');

        // Operational Reports
        Route::get('/reports/jobs', [ReportController::class, 'jobs'])->name('reports.jobs');
        Route::get('/reports/workers', [ReportController::class, 'workers'])->name('reports.workers');
    });

    // Ledger Viewing & Export Routes (Super Admin, Accounts & Operations)
    Route::middleware('role:super_admin,accounts,operations')->group(function () {
        // Worker Ledgers (View & Export)
        Route::get('/ledger', [WorkerLedgerController::class, 'index'])->name('ledger.index');
        Route::get('/ledger/worker/{worker}', [WorkerLedgerController::class, 'show'])->name('ledger.show');
        Route::get('/ledger/worker/{worker}/export-csv', [WorkerLedgerController::class, 'exportCsv'])->name('ledger.export');

        // Brand Ledgers & Receivables (View & Export)
        Route::get('/brand-ledger', [BrandLedgerController::class, 'index'])->name('brand-ledger.index');
        Route::get('/brand-ledger/{brand}/export', [BrandLedgerController::class, 'exportExcel'])->name('brand-ledger.export');
    });

    // Accounts & Super Admin Financial Management Routes
    Route::middleware('role:super_admin,accounts')->group(function () {
        // Worker Ledger Actions (Manual Adjustments & Reversals)
        Route::post('/ledger/worker/{worker}/transaction', [WorkerLedgerController::class, 'postManualTransaction'])->name('ledger.transaction');
        Route::post('/ledger/transaction/{transaction}/reverse', [WorkerLedgerController::class, 'reverseTransaction'])->name('ledger.reverse');

        // Brand Ledger Actions (Payments, Adjustments & Reversals)
        Route::post('/brand-ledger/{brand}/payment', [BrandLedgerController::class, 'storePayment'])->name('brand-ledger.payment');
        Route::post('/brand-ledger/{brand}/adjustment', [BrandLedgerController::class, 'storeAdjustment'])->name('brand-ledger.adjustment');
        Route::post('/brand-ledger/{brandLedger}/reverse', [BrandLedgerController::class, 'reverse'])->name('brand-ledger.reverse');

        // Worker Payments
        Route::resource('payments', WorkerPaymentController::class)->only(['index', 'create', 'store']);

        // Financial Reports
        Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('/reports/ledger', [ReportController::class, 'ledger'])->name('reports.ledger');
    });

    // Super Admin Only
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::post('/users/{user}/password', [UserController::class, 'quickPasswordUpdate'])->name('users.password.update');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/{filename}', [BackupController::class, 'download'])->name('backups.download');

        // One-Click Database Migration & Cache Clear for Shared Hosting / cPanel
        Route::get('/system/migrate', function () {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $migOutput = \Illuminate\Support\Facades\Artisan::output();

            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            $optOutput = \Illuminate\Support\Facades\Artisan::output();

            return response("
                <div style='font-family: Arial, sans-serif; max-width: 650px; margin: 40px auto; padding: 25px; background: #0f172a; color: #f8fafc; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);'>
                    <h2 style='color: #10b981; margin-top: 0;'>&#10004; Database Migration & Cache Update Completed</h2>
                    <p style='color: #94a3b8; font-size: 13px;'>The new database columns and tables were applied safely to your live server.</p>
                    <pre style='background: #1e293b; color: #38bdf8; padding: 15px; border-radius: 8px; font-size: 12px; line-height: 1.5; white-space: pre-wrap;'>" . htmlspecialchars($migOutput . "\n" . $optOutput) . "</pre>
                    <div style='margin-top: 20px;'>
                        <a href='" . route('dashboard') . "' style='display: inline-block; padding: 10px 20px; background: #10b981; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 13px;'>&larr; Back to Dashboard</a>
                    </div>
                </div>
            ");
        })->name('system.migrate');
    });
});
