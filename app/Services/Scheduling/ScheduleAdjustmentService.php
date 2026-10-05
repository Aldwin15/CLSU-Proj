<?php

namespace App\Services\Scheduling;

use App\Models\Schedule;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;

class ScheduleAdjustmentService
{
    /**
     * Reschedule an activity and cascade changes to successor tasks (Finish-to-Start CPM)
     */
    public function rescheduleWithSuccessors(
        Schedule $schedule,
        string $newStartDate,
        bool $adjustSuccessors = true,
        ?User $user = null,
        ?array $weatherSnapshot = null
    ): array {
        $oldStartDate = $schedule->current_start_date->format('M d');
        $newStart = Carbon::parse($newStartDate);
        $duration = $schedule->duration_days;
        $newEnd = (clone $newStart)->addDays($duration - 1);

        // Update target schedule
        $schedule->update([
            'current_start_date' => $newStart,
            'current_end_date' => $newEnd,
            'has_weather_alert' => false,
            'feasibility' => 'Feasible',
            'status' => 'Scheduled',
            'continued_same_day' => false,
            'resume_time' => null,
            'mitigation_notes' => null,
        ]);

        $adjustedSuccessorsCount = 0;

        // Propagate to successors if enabled
        if ($adjustSuccessors) {
            $adjustedSuccessorsCount = $this->propagateToSuccessors($schedule, $newEnd);
        }

        // Create immutable audit log entry
        AuditLog::create([
            'company_id' => $schedule->project->company_id ?? 1,
            'user_id' => $user->id ?? null,
            'project_id' => $schedule->project_id,
            'schedule_id' => $schedule->id,
            'user_name' => $user->name ?? 'Project Engineer',
            'user_role' => $user->role ?? 'project_engineer',
            'category' => 'Weather Decision',
            'action_title' => 'Rescheduled After Alert',
            'target_activity' => $schedule->name,
            'weather_snapshot' => $weatherSnapshot ?? [
                'rain_probability' => 75,
                'condition' => 'Precipitation Exceeded Threshold',
                'rescheduled_to' => $newStart->format('M d, Y')
            ],
            'outcome_type' => 'Reschedule',
            'outcome_details' => "Shifted {$oldStartDate} → {$newStart->format('M d')}. " . ($adjustSuccessors ? "({$adjustedSuccessorsCount} Successors Adjusted)" : "(Successors Retained)"),
            'response_time_minutes' => 12
        ]);

        return [
            'success' => true,
            'schedule' => $schedule->fresh(['masterActivity', 'predecessor', 'successors']),
            'adjusted_successors_count' => $adjustedSuccessorsCount,
            'new_start_date' => $newStart->format('M d'),
            'new_end_date' => $newEnd->format('M d')
        ];
    }

    /**
     * Recursively update downstream successor start and end dates (Finish-to-Start)
     */
    protected function propagateToSuccessors(Schedule $parentSchedule, Carbon $parentEndDate): int
    {
        $count = 0;
        $successors = Schedule::where('predecessor_id', $parentSchedule->id)->get();

        foreach ($successors as $successor) {
            $successorStart = (clone $parentEndDate)->addDay();
            $successorEnd = (clone $successorStart)->addDays($successor->duration_days - 1);

            $successor->update([
                'current_start_date' => $successorStart,
                'current_end_date' => $successorEnd,
                'has_weather_alert' => false,
                'feasibility' => 'Feasible'
            ]);

            $count++;
            $count += $this->propagateToSuccessors($successor, $successorEnd);
        }

        return $count;
    }

    /**
     * Continue same day with mitigation
     */
    public function continueSameDay(
        Schedule $schedule,
        string $resumeTime,
        string $mitigationNotes,
        ?User $user = null
    ): array {
        $schedule->update([
            'has_weather_alert' => false,
            'feasibility' => 'Feasible',
            'status' => 'In Progress',
            'continued_same_day' => true,
            'resume_time' => $resumeTime,
            'mitigation_notes' => $mitigationNotes,
        ]);

        AuditLog::create([
            'company_id' => $schedule->project->company_id ?? 1,
            'user_id' => $user->id ?? null,
            'project_id' => $schedule->project_id,
            'schedule_id' => $schedule->id,
            'user_name' => $user->name ?? 'Project Engineer',
            'user_role' => $user->role ?? 'project_engineer',
            'category' => 'Weather Decision',
            'action_title' => 'Continued Same Day',
            'target_activity' => $schedule->name,
            'weather_snapshot' => [
                'mitigation' => $mitigationNotes,
                'resume_time' => $resumeTime
            ],
            'outcome_type' => 'Continue Same Day',
            'outcome_details' => "Mitigation confirmed: {$mitigationNotes}. Work resuming at {$resumeTime}.",
            'response_time_minutes' => 5
        ]);

        return [
            'success' => true,
            'schedule' => $schedule->fresh()
        ];
    }
}
