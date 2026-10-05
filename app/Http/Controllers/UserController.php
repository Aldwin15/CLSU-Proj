<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->get('company_id');
        $query = User::with('company');

        if ($companyId && $companyId !== 'ALL') {
            $query->where('company_id', $companyId);
        }

        return response()->json([
            'success' => true,
            'users' => $query->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|string',
            'password' => 'nullable|string|min:6',
            'custom_permissions' => 'nullable|array'
        ]);

        $user = User::create([
            'company_id' => $validated['company_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password'] ?? 'password'),
            'custom_permissions' => $validated['custom_permissions'] ?? null,
            'status' => 'Active'
        ]);

        AuditLog::create([
            'company_id' => $user->company_id,
            'user_id' => Auth::id(),
            'category' => 'System Admin',
            'action_title' => 'New User Account Created',
            'target_activity' => "User: {$user->name}",
            'outcome_type' => 'User Created',
            'outcome_details' => "Created {$user->role} account for {$user->name} with customized permissions",
            'logged_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user->load('company')
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string',
            'status' => 'nullable|string',
            'custom_permissions' => 'nullable|array'
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user->load('company')
        ]);
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete the currently authenticated session user.'
            ], 422);
        }

        if (User::count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'At least one user account must remain active.'
            ], 422);
        }

        $userName = $user->name;
        $user->delete();

        AuditLog::create([
            'company_id' => session('active_company_id', 1),
            'user_id' => Auth::id(),
            'category' => 'System Admin',
            'action_title' => 'User Account Revoked',
            'target_activity' => "User: {$userName}",
            'outcome_type' => 'User Deleted',
            'outcome_details' => "Removed privileges for {$userName}",
            'logged_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User removed successfully'
        ]);
    }
}
