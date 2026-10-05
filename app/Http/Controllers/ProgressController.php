<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProgressRecord;
use App\Models\Schedule;
use App\Models\AuditLog;
use Carbon\Carbon;

class ProgressController extends Controller
{
    /**
     * Get progress records for a project
     */
    public function index(Request $request)
    {
        $projectId = $request->get('project_id', session('active_project_id', 1));
        
        $records = ProgressRecord::with(['schedule.masterActivity'])
            ->whereHas('schedule', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })
            ->latest('record_date')
            ->get();

        return response()->json([
            'success' => true,
            'progress_records' => $records
        ]);
    }

    /**
     * Store or update daily progress record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'actual_progress' => 'required|numeric|min:0|max:100',
            'record_date' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);

        $schedule = Schedule::with(['project.company', 'masterActivity'])->findOrFail($validated['schedule_id']);
        $user = Auth::user();

        $recordDate = !empty($validated['record_date']) ? Carbon::parse($validated['record_date']) : Carbon::today();
        
        // Find existing record for this date or most recent record to preserve planned progress
        $latestRecord = ProgressRecord::where('schedule_id', $schedule->id)->latest('id')->first();
        $record = ProgressRecord::where('schedule_id', $schedule->id)
            ->whereDate('record_date', $recordDate)
            ->first();

        $plannedProgress = $record ? $record->planned_progress : ($latestRecord ? $latestRecord->planned_progress : 50.0);
        $actualProgress = (float) $validated['actual_progress'];
        $variance = round($actualProgress - $plannedProgress, 1);
        
        $status = 'On Track';
        if ($variance > 0) {
            $status = 'Ahead of Schedule';
        } elseif ($variance < -5) {
            $status = 'Critical Delay';
        } elseif ($variance < 0) {
            $status = 'Behind Schedule';
        }

        if ($record) {
            $record->update([
                'user_id' => $user ? $user->id : $record->user_id,
                'actual_progress' => $actualProgress,
                'variance' => $variance,
                'status' => $status,
                'notes' => $validated['notes'] ?? $record->notes
            ]);
        } else {
            $record = ProgressRecord::create([
                'schedule_id' => $schedule->id,
                'user_id' => $user ? $user->id : null,
                'record_date' => $recordDate,
                'planned_progress' => $plannedProgress,
                'actual_progress' => $actualProgress,
                'variance' => $variance,
                'status' => $status,
                'notes' => $validated['notes'] ?? 'Daily accomplishment entry'
            ]);
        }

        // Immutable Audit Log
        AuditLog::create([
            'company_id' => $schedule->project->company_id,
            'user_id' => $user ? $user->id : null,
            'project_id' => $schedule->project_id,
            'user_name' => $user ? $user->name : 'Site Engineer',
            'user_role' => $user ? ($user->role === 'site_supervisor' ? 'Site Supervisor' : 'Project Engineer') : 'Site Supervisor',
            'category' => 'Progress Update',
            'action_title' => 'Daily Progress Accomplishment Submitted',
            'target_activity' => $schedule->name,
            'outcome_type' => 'Progress Saved',
            'outcome_details' => "Updated actual progress to {$actualProgress}% (Variance: " . ($variance >= 0 ? '+' : '') . "{$variance}%)",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daily progress record saved and logged to audit trail.',
            'record' => $record
        ]);
    }
}
