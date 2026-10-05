<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\AuditLog;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::withCount(['projects', 'users']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->get('status') !== 'ALL') {
            $query->where('status', $request->get('status'));
        }

        $companies = $query->get();

        return response()->json([
            'success' => true,
            'companies' => $companies
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:Active,Inactive',
            'logo_url' => 'nullable|string|max:500',
        ]);

        $company = Company::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'contact_person' => $validated['contact_person'] ?? null,
            'contact_email' => strtolower(trim($validated['contact_email'])),
            'contact_phone' => $validated['contact_phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'] ?? 'Active',
            'logo_url' => $validated['logo_url'] ?? null,
        ]);

        // Automatically provision contractor account with default password 'password123'
        $contactName = !empty($validated['contact_person']) ? $validated['contact_person'] : ($validated['name'] . ' Representative');
        $words = explode(' ', trim($contactName));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= strtoupper(substr($w, 0, 1));
        }
        $initials = $initials ?: 'CR';

        \App\Models\User::firstOrCreate(
            ['email' => strtolower(trim($validated['contact_email']))],
            [
                'company_id' => $company->id,
                'name' => $contactName,
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'contractor',
                'initials' => $initials,
                'status' => 'Active',
            ]
        );

        $user = Auth::user();
        AuditLog::create([
            'company_id' => $company->id,
            'user_id' => $user->id ?? 1,
            'user_name' => $user->name ?? 'Administrator',
            'user_role' => $user->role ?? 'admin',
            'category' => 'System Admin',
            'action_title' => 'Contractor Organization & User Account Registered',
            'target_activity' => "Company: {$company->name} ({$company->code})",
            'outcome_type' => 'Company Registered',
            'outcome_details' => "Registered contractor {$company->name} and provisioned login account for {$validated['contact_email']} with default password 'password123'",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contractor company and representative account (password: password123) registered successfully',
            'company' => $company->loadCount(['projects', 'users'])
        ]);
    }

    public function show(Company $company)
    {
        return response()->json([
            'success' => true,
            'company' => $company->load(['projects', 'users'])->loadCount(['projects', 'users'])
        ]);
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code,' . $company->id,
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:Active,Inactive',
            'logo_url' => 'nullable|string|max:500',
        ]);

        $company->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'contact_person' => $validated['contact_person'] ?? $company->contact_person,
            'contact_email' => $validated['contact_email'] ?? $company->contact_email,
            'contact_phone' => $validated['contact_phone'] ?? $company->contact_phone,
            'address' => $validated['address'] ?? $company->address,
            'status' => $validated['status'] ?? $company->status,
            'logo_url' => $validated['logo_url'] ?? $company->logo_url,
        ]);

        $user = Auth::user();
        AuditLog::create([
            'company_id' => $company->id,
            'user_id' => $user->id ?? 1,
            'user_name' => $user->name ?? 'Administrator',
            'user_role' => $user->role ?? 'admin',
            'category' => 'System Admin',
            'action_title' => 'Contractor Profile Updated',
            'target_activity' => "Company: {$company->name} ({$company->code})",
            'outcome_type' => 'Company Updated',
            'outcome_details' => "Updated credentials & contact info for {$company->name}",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contractor company profile updated successfully',
            'company' => $company->fresh()->loadCount(['projects', 'users'])
        ]);
    }

    public function destroy(Company $company)
    {
        if (strtoupper($company->code) === 'PPSDS') {
            return response()->json([
                'success' => false,
                'message' => 'The primary university physical plant department (PPSDS) cannot be removed.'
            ], 422);
        }

        if (Company::count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'At least one organization / contractor record must remain active in the system.'
            ], 422);
        }

        $companyName = $company->name;
        $companyCode = $company->code;
        $companyId = $company->id;

        // Reassign any projects/users to PPSDS (company 1) if deleted
        $ppsds = Company::where('code', 'PPSDS')->first() ?? Company::first();
        if ($ppsds && $ppsds->id !== $companyId) {
            $company->projects()->update(['company_id' => $ppsds->id]);
            $company->users()->update(['company_id' => $ppsds->id]);
        }

        $company->delete();

        $user = Auth::user();
        AuditLog::create([
            'company_id' => $ppsds->id ?? 1,
            'user_id' => $user->id ?? 1,
            'user_name' => $user->name ?? 'Administrator',
            'user_role' => $user->role ?? 'admin',
            'category' => 'System Admin',
            'action_title' => 'Contractor Organization Removed',
            'target_activity' => "Company: {$companyName} ({$companyCode})",
            'outcome_type' => 'Company Deleted',
            'outcome_details' => "Removed contractor organization {$companyName} from university directory",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contractor organization deleted successfully'
        ]);
    }
}
