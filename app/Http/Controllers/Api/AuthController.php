<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_token' => ['nullable', 'string'],
        ]);

        $rawPhone = trim($validated['phone']);
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        $last10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

        $worker = Worker::where('phone', $rawPhone)
            ->orWhere('phone', $cleanPhone)
            ->orWhere('phone', '0' . $last10)
            ->orWhere('phone', '92' . $last10)
            ->orWhere('phone', '+92' . $last10)
            ->first();

        if (!$worker || !Hash::check($validated['password'], $worker->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number or password/PIN.',
                'data' => null,
            ], 401);
        }

        if ($worker->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => "Your worker account is currently {$worker->status}. Please contact SerWizo support.",
                'data' => null,
            ], 403);
        }

        if (!empty($validated['device_token'])) {
            $worker->update([
                'device_token' => $validated['device_token'],
                'last_active_at' => now(),
            ]);
        } else {
            $worker->update(['last_active_at' => now()]);
        }

        $worker->load(['primaryCity', 'categories', 'bankDetail']);

        // Generate Sanctum token
        $token = $worker->createToken('worker-app-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully.',
            'data' => [
                'token' => $token,
                'worker' => [
                    'id' => $worker->worker_code,
                    'database_id' => $worker->id,
                    'worker_code' => $worker->worker_code,
                    'name' => $worker->name,
                    'phone' => $worker->phone,
                    'mobile' => $worker->phone,
                    'email' => $worker->email,
                    'city' => $worker->primaryCity?->name ?? 'Lahore',
                    'primary_city' => $worker->primaryCity?->name ?? 'Lahore',
                    'skill_level' => $worker->skill_level,
                    'skills' => $worker->categories->pluck('title')->all() ?: ['General Maintenance'],
                    'status' => strtoupper($worker->status),
                    'availability' => strtoupper($worker->availability ?? 'AVAILABLE'),
                    'photo_url' => $worker->photo_url,
                    'cnic_verified' => true,
                    'reference_verified' => true,
                    'technical_evaluation_passed' => true,
                    'terms_accepted' => true,
                ],
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
            'data' => null,
        ]);
    }
}
