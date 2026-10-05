<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterActivity;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class MasterActivityController extends Controller
{
    public function index()
    {
        $activities = MasterActivity::all();
        return response()->json([
            'success' => true,
            'activities' => $activities
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:master_activities,code',
            'category' => 'required|string',
            'default_duration_days' => 'required|integer|min:1',
            'max_rain_probability' => 'required|numeric|min:0|max:100',
            'max_rain_volume_mm' => 'nullable|numeric|min:0',
            'max_wind_speed_kmh' => 'required|numeric|min:0',
            'safety_trigger' => 'nullable|string',
            'predecessor_hint' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        $activity = MasterActivity::create($validated);

        AuditLog::create([
            'company_id' => session('active_company_id', 1),
            'user_id' => Auth::id(),
            'category' => 'System Admin',
            'action_title' => 'New Master Activity Registered',
            'target_activity' => "Catalog: {$activity->name}",
            'outcome_type' => 'Activity Created',
            'outcome_details' => "Added {$activity->name} ({$activity->category}) to library",
            'logged_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Master activity created successfully',
            'activity' => $activity
        ]);
    }

    public function update(Request $request, MasterActivity $masterActivity)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:master_activities,code,' . $masterActivity->id,
            'category' => 'required|string',
            'default_duration_days' => 'required|integer|min:1',
            'max_rain_probability' => 'required|numeric|min:0|max:100',
            'max_rain_volume_mm' => 'nullable|numeric|min:0',
            'max_wind_speed_kmh' => 'required|numeric|min:0',
            'safety_trigger' => 'nullable|string',
            'predecessor_hint' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        $masterActivity->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Master activity updated successfully',
            'activity' => $masterActivity
        ]);
    }

    public function destroy(MasterActivity $masterActivity)
    {
        if (MasterActivity::count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'At least one predefined construction activity must remain in the master catalog.'
            ], 422);
        }

        $masterActivity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Master activity removed successfully'
        ]);
    }
}
