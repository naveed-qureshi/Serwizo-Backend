@extends('layouts.app')

@section('title', 'Database Backups')
@section('header_title', 'Database Backups & Recovery')
@section('header_subtitle', 'Automated Daily Snapshots & Disaster Recovery Management')

@section('content')
<div class="space-y-6 pt-4 text-xs">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-800 uppercase tracking-wider">Database Snapshot Manager</h3>
            <p class="text-slate-500 text-[11px] mt-0.5">Automated local and offsite storage backups</p>
        </div>

        <form action="{{ route('backups.create') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-database mr-1"></i> Generate Manual Backup Now
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase text-[10px]">
                <tr>
                    <th class="py-3 px-4">Backup File Name</th>
                    <th class="py-3 px-4">File Size</th>
                    <th class="py-3 px-4">Created Timestamp</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($backups as $b)
                <tr class="hover:bg-slate-50/80 font-mono">
                    <td class="py-3 px-4 font-bold text-slate-800 flex items-center space-x-2">
                        <i class="fa-solid fa-file-zipper text-emerald-600"></i>
                        <span>{{ $b['filename'] }}</span>
                    </td>
                    <td class="py-3 px-4 text-slate-600 font-sans">{{ $b['size'] }}</td>
                    <td class="py-3 px-4 text-slate-500 font-sans">{{ $b['created_at'] }}</td>
                    <td class="py-3 px-4 text-right font-sans">
                        <a href="{{ route('backups.download', $b['filename']) }}" 
                           class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold rounded-lg text-xs transition inline-flex items-center space-x-1">
                            <i class="fa-solid fa-download"></i>
                            <span>Download</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-8 text-center text-slate-400 font-sans">No backups generated yet. Click "Generate Manual Backup Now" to create your first snapshot.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
