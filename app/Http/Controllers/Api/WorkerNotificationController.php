<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Worker;
use Illuminate\Http\Request;

class WorkerNotificationController extends Controller
{
    public function index(Request $request)
    {
        $worker = $request->user();

        $notifications = AppNotification::where('notifiable_type', Worker::class)
            ->where('notifiable_id', $worker->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = AppNotification::where('notifiable_type', Worker::class)
            ->where('notifiable_id', $worker->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $unreadCount,
                'notifications' => $notifications->through(fn($n) => [
                    'id' => $n->id,
                    'title' => $n->title,
                    'body' => $n->body,
                    'type' => $n->type,
                    'data' => $n->data,
                    'is_read' => $n->read_at !== null,
                    'created_at' => $n->created_at->toIso8601String(),
                ]),
            ],
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $worker = $request->user();

        $notification = AppNotification::where('notifiable_type', Worker::class)
            ->where('notifiable_id', $worker->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }
}
