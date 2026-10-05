<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use App\Models\Project;
use App\Models\MasterActivity;
use App\Services\Scheduling\ScheduleAdjustmentService;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    protected ScheduleAdjustmentService $adjustmentService;

    public function __construct(ScheduleAdjustmentService $adjustmentService)
    {
        $this->adjustmentService = $adjustmentService;
    }

    /**
     * Get all schedules for a project (JSON API for Gantt)
     */
    public function index(Request $request)
    {
        $projectId = $request->get('project_id', session('active_project_id', 1));
        
        $schedules = Schedule::with(['masterActivity', 'predecessor', 'weatherThreshold'])
            ->where('project_id', $projectId)
            ->get();

        return response()->json([
            'success' => true,
            'schedules' => $schedules
        ]);
    }

    /**
     * Store new activity to project Gantt schedule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'predecessor_id' => 'nullable|exists:schedules,id',
            'master_activity_id' => 'nullable|exists:master_activities,id',
            'duration_days' => 'required|integer|min:1|max:30',
            'start_date' => 'required|date',
            'weather_rule_text' => 'nullable|string'
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = (clone $start)->addDays($validated['duration_days'] - 1);

        $schedule = Schedule::create([
            'project_id' => $validated['project_id'],
            'master_activity_id' => $validated['master_activity_id'] ?? null,
            'predecessor_id' => $validated['predecessor_id'] ?? null,
            'name' => $validated['name'],
            'duration_days' => $validated['duration_days'],
            'baseline_start_date' => $start,
            'baseline_end_date' => $end,
            'current_start_date' => $start,
            'current_end_date' => $end,
            'feasibility' => 'Feasible',
            'has_weather_alert' => false,
            'weather_rule_text' => $validated['weather_rule_text'] ?? 'Rain < 40%, Wind < 40km/h',
            'status' => 'Scheduled'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activity successfully added to Gantt schedule.',
            'schedule' => $schedule->load(['masterActivity', 'predecessor'])
        ]);
    }

    /**
     * Handle Reschedule Decision from Drawer
     */
    public function reschedule(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'new_start_date' => 'required|string',
            'successor_action' => 'required|string|in:adjust,retain',
            'weather_snapshot' => 'nullable|array'
        ]);

        $result = $this->adjustmentService->rescheduleWithSuccessors(
            $schedule,
            $validated['new_start_date'],
            $validated['successor_action'] === 'adjust',
            Auth::user(),
            $validated['weather_snapshot'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Handle Continue Same Day Decision from Drawer
     */
    public function continueSameDay(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'resume_time' => 'required|string',
            'mitigation_notes' => 'nullable|string'
        ]);

        $result = $this->adjustmentService->continueSameDay(
            $schedule,
            $validated['resume_time'],
            $validated['mitigation_notes'] ?? 'On-site mitigation applied',
            Auth::user()
        );

        return response()->json($result);
    }

    /**
     * Delete/Remove scheduled activity from Gantt schedule
     */
    public function destroy(Schedule $schedule)
    {
        $name = $schedule->name;
        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => "Activity '{$name}' removed from Gantt schedule."
        ]);
    }
}
