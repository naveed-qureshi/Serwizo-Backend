<?php

namespace App\Http\Controllers;

use App\Models\WorkerDocument;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WorkerDocumentController extends Controller
{
    public function show(WorkerDocument $document)
    {
        // Restricted access: only Super Admin or Operations
        if (!in_array(Auth::user()->role, ['super_admin', 'operations'])) {
            abort(403, 'Unauthorized access to worker sensitive documents.');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            // Return placeholder or 404
            abort(404, 'Document file not found.');
        }

        return response()->file(Storage::disk('public')->path($document->file_path));
    }

    public function verify(WorkerDocument $document)
    {
        $document->update([
            'is_verified' => true,
            'verified_by_user_id' => Auth::id(),
            'verified_at' => now(),
        ]);

        AuditLogService::log(
            action: 'updated',
            module: 'workers',
            recordId: $document->worker_id,
            description: "Verified {$document->document_type} for worker #{$document->worker_id}"
        );

        return back()->with('success', 'Document verified successfully.');
    }
}
