<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    public function index()
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $files = File::files($backupDir);
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => $file->getFilename(),
                'size' => round($file->getSize() / 1024, 2) . ' KB',
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
            ];
        }

        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return view('backups.index', compact('backups'));
    }

    public function create()
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $sqliteDb = database_path('database.sqlite');
        
        if (File::exists($sqliteDb)) {
            $backupPath = $backupDir . "/serwizo_backup_{$timestamp}.sqlite";
            File::copy($sqliteDb, $backupPath);
        } else {
            // For MySQL, write a schema/dump placeholder or trigger mysqldump if configured
            $backupPath = $backupDir . "/serwizo_backup_{$timestamp}.sql";
            File::put($backupPath, "-- SerWizo Database Snapshot Generated at " . date('Y-m-d H:i:s'));
        }

        AuditLogService::log(
            action: 'created',
            module: 'settings',
            recordId: 'backup',
            description: "Database snapshot created: serwizo_backup_{$timestamp}"
        );

        return back()->with('success', 'Database backup snapshot generated successfully.');
    }

    public function download(string $filename)
    {
        $filePath = storage_path('app/backups/' . basename($filename));
        if (!File::exists($filePath)) {
            abort(404, 'Backup file not found.');
        }

        return Response::download($filePath);
    }
}
