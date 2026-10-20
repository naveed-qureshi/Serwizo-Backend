<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('brand')->orderBy('name')->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $brands = Brand::where('status', 'active')->orderBy('name')->get();
        return view('users.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', Rule::in(['super_admin', 'operations', 'accounts', 'brand'])],
            'brand_id' => ['nullable', 'required_if:role,brand', 'exists:brands,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['required', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'brand_id' => $validated['role'] === 'brand' ? ($validated['brand_id'] ?? null) : null,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'visible_password' => $validated['password'],
            'is_active' => $validated['is_active'],
        ]);

        AuditLogService::log(
            action: 'created',
            module: 'users',
            recordId: $user->id,
            description: "Created user {$user->name} with role {$user->role}" . ($user->brand_id ? " (Brand #{$user->brand_id})" : "")
        );

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $brands = Brand::where('status', 'active')->orderBy('name')->get();
        return view('users.edit', compact('user', 'brands'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['super_admin', 'operations', 'accounts', 'brand'])],
            'brand_id' => ['nullable', 'required_if:role,brand', 'exists:brands,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['required', 'boolean'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'brand_id' => $validated['role'] === 'brand' ? ($validated['brand_id'] ?? null) : null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
            $updateData['visible_password'] = $validated['password'];
        }

        $user->update($updateData);

        AuditLogService::log(
            action: 'updated',
            module: 'users',
            recordId: $user->id,
            description: "Updated user {$user->name} (Role: {$user->role})"
        );

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function quickPasswordUpdate(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
            'visible_password' => $validated['password'],
        ]);

        AuditLogService::log(
            action: 'password_reset',
            module: 'users',
            recordId: $user->id,
            description: "Admin reset password for user {$user->name} ({$user->email})"
        );

        return back()->with('success', "Password for {$user->name} has been updated successfully.");
    }
}
