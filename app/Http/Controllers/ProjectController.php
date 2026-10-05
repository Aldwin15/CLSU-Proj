<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Company;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->get('company_id', session('active_company_id', 1));
        $projects = Project::where('company_id', $companyId)->with('company')->get();

        return response()->json([
            'success' => true,
            'projects' => $projects
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects,code',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_storeys' => 'nullable|integer|min:1',
            'baseline_duration_weeks' => 'nullable|integer|min:1'
        ]);

        $project = Project::create([
            'company_id' => $validated['company_id'],
            'name' => $validated['name'],
            'code' => $validated['code'],
            'location' => $validated['location'],
            'latitude' => $validated['latitude'] ?? 15.7144,
            'longitude' => $validated['longitude'] ?? 120.9307,
            'storeys' => $validated['total_storeys'] ?? $validated['storeys'] ?? 5,
            'duration_weeks' => $validated['baseline_duration_weeks'] ?? $validated['duration_weeks'] ?? 12,
            'status' => 'Active'
        ]);

        AuditLog::create([
            'company_id' => $project->company_id,
            'user_id' => Auth::id(),
            'project_id' => $project->id,
            'category' => 'Schedule Change',
            'action_title' => 'Project Workspace Created',
            'target_activity' => $project->name,
            'outcome_type' => 'Schedule Approved',
            'outcome_details' => "Registered project workspace {$project->name} ({$project->code})",
            'logged_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'project' => $project
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects,code,' . $project->id,
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'total_storeys' => 'nullable|integer|min:1',
            'storeys' => 'nullable|integer|min:1',
            'baseline_duration_weeks' => 'nullable|integer|min:1',
            'duration_weeks' => 'nullable|integer|min:1'
        ]);

        $data = [
            'name' => $validated['name'],
            'code' => $validated['code'],
            'location' => $validated['location'],
        ];

        if (isset($validated['latitude'])) $data['latitude'] = $validated['latitude'];
        if (isset($validated['longitude'])) $data['longitude'] = $validated['longitude'];
        if (isset($validated['total_storeys']) || isset($validated['storeys'])) {
            $data['storeys'] = $validated['total_storeys'] ?? $validated['storeys'];
        }
        if (isset($validated['baseline_duration_weeks']) || isset($validated['duration_weeks'])) {
            $data['duration_weeks'] = $validated['baseline_duration_weeks'] ?? $validated['duration_weeks'];
        }

        $project->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'project' => $project
        ]);
    }

    public function destroy(Project $project)
    {
        if (Project::count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'At least one project workspace must remain active.'
            ], 422);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully'
        ]);
    }
}
