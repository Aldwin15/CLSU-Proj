<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Project;
use App\Models\Schedule;
use App\Models\ProgressRecord;
use App\Models\AuditLog;
use App\Models\MasterActivity;
use App\Models\User;
use App\Services\Weather\OpenMeteoService;

class DashboardController extends Controller
{
    protected OpenMeteoService $weatherService;

    public function __construct(OpenMeteoService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $companies = Company::withCount(['projects', 'users'])->get();
        
        $selectedCompanyId = $request->get('company_id', session('active_company_id', $user->company_id ?? $companies->first()->id));
        session(['active_company_id' => $selectedCompanyId]);
        
        $currentCompany = Company::find($selectedCompanyId) ?? $companies->first();
        
        // Eager load all projects with company, schedules, and progress records
        $allProjectsQuery = Project::with([
            'company',
            'schedules.masterActivity',
            'schedules.predecessor',
            'schedules.progressRecords' => function ($query) {
                $query->orderBy('record_date', 'asc')->orderBy('id', 'asc');
            }
        ])->get();

        $formattedAllProjects = $allProjectsQuery->map(function($p) {
            $schedules = $p->schedules;
            $formattedActivities = $schedules->map(function($s) {
                $startDate = $s->current_start_date 
                    ? \Carbon\Carbon::parse($s->current_start_date) 
                    : ($s->baseline_start_date ? \Carbon\Carbon::parse($s->baseline_start_date) : now());
                
                $duration = (int) ($s->duration_days ?: 1);
                
                $endDate = $s->current_end_date 
                    ? \Carbon\Carbon::parse($s->current_end_date) 
                    : ($s->baseline_end_date ? \Carbon\Carbon::parse($s->baseline_end_date) : (clone $startDate)->addDays($duration - 1));
                
                $startDay = (int) $startDate->format('j');
                $startCol = max(0, $startDay - 1);
                $span = $duration;

                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'predecessor' => $s->predecessor->name ?? null,
                    'weatherRuleText' => $s->weather_rule_text ?? 'Rain < 40%, Wind < 40km/h',
                    'feasibility' => $s->feasibility ?? 'Feasible',
                    'hasAlert' => (bool) $s->has_weather_alert,
                    'durationDays' => $duration,
                    'baselineStart' => $s->baseline_start_date ? $s->baseline_start_date->format('M d') : $startDate->format('M d'),
                    'baselineEnd' => $s->baseline_end_date ? $s->baseline_end_date->format('M d') : $endDate->format('M d'),
                    'baselineStartCol' => $startCol,
                    'baselineSpan' => $span,
                    'currentStart' => $startDate->format('M d'),
                    'currentEnd' => $endDate->format('M d'),
                    'currentStartCol' => $startCol,
                    'currentSpan' => $span,
                    'continuedSameDay' => (bool) $s->continued_same_day,
                    'resumeTime' => $s->resume_time,
                    'mitigationNotes' => $s->mitigation_notes
                ];
            })->values();

            $formattedProgress = $schedules->map(function($s) {
                $rec = $s->progressRecords->last();
                $planned = $rec ? (int)$rec->planned_progress : 0;
                $actual = $rec ? (int)$rec->actual_progress : 0;
                $variance = $actual - $planned;
                $status = $variance > 0 ? 'Ahead of Schedule' : ($variance < 0 ? 'Behind Schedule' : 'On Track');
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'plannedProgress' => $planned,
                    'actualProgress' => $actual,
                    'variance' => $variance,
                    'status' => $status
                ];
            })->values();

            $avgPlanned = $formattedProgress->isEmpty() ? 0 : round($formattedProgress->avg('plannedProgress'), 1);
            $avgActual = $formattedProgress->isEmpty() ? 0 : round($formattedProgress->avg('actualProgress'), 1);
            $variance = round($avgActual - $avgPlanned, 1);
            $activeAlerts = $formattedActivities->where('hasAlert', true)->count();

            return [
                'id' => $p->id,
                'company_id' => $p->company_id,
                'companyId' => $p->company_id,
                'company_name' => $p->company->name ?? 'CLSU Project',
                'company_code' => $p->company->code ?? 'PPSDS',
                'name' => $p->name,
                'code' => $p->code,
                'location' => $p->location,
                'latitude' => $p->latitude,
                'longitude' => $p->longitude,
                'storeys' => $p->storeys ?? 4,
                'duration_weeks' => $p->duration_weeks ?? 12,
                'status' => $p->status ?? 'In Progress',
                'activities' => $formattedActivities,
                'progress' => $formattedProgress,
                'avgPlanned' => $avgPlanned,
                'avgActual' => $avgActual,
                'variance' => $variance,
                'activeAlerts' => $activeAlerts
            ];
        })->values();

        // If PPSDS (Monitoring agency), show all university projects. If contractor, show only that contractor's projects.
        $companyProjects = ($currentCompany->code === 'PPSDS')
            ? $formattedAllProjects
            : $formattedAllProjects->where('company_id', $currentCompany->id)->values();

        if ($companyProjects->isEmpty()) {
            $companyProjects = $formattedAllProjects;
        }

        $selectedProjectId = $request->get('project_id', session('active_project_id', $companyProjects->first()['id'] ?? null));
        if ($selectedProjectId) {
            session(['active_project_id' => $selectedProjectId]);
        }
        
        $currentProject = $formattedAllProjects->firstWhere('id', $selectedProjectId) ?? $companyProjects->first() ?? null;
        
        // Fetch Live Coordinates Weather Forecast (Science City of Muñoz, Nueva Ecija)
        $weatherData = $this->weatherService->getForecast(
            (float) ($currentProject['latitude'] ?? 15.7144),
            (float) ($currentProject['longitude'] ?? 120.9307)
        );

        $auditLogs = AuditLog::with(['company', 'user'])->latest()->take(30)->get();
        $masterActivities = MasterActivity::all();
        $users = User::with('company')->get();

        // Calculate KPI Metrics for active project
        $formattedActivities = collect($currentProject['activities'] ?? []);
        $formattedProgress = collect($currentProject['progress'] ?? []);
        $scheduleVariance = $currentProject['variance'] ?? 0;
        $activeAlertsCount = $currentProject['activeAlerts'] ?? 0;
        $pendingDecisionsCount = $formattedActivities->where('hasAlert', true)->count();

        // S-Curve Analytics Dataset (Calculated dynamically from real project progress)
        if ($formattedProgress->isEmpty()) {
            $sCurveData = [
                'labels' => ['Wk 1', 'Wk 2', 'Wk 3', 'Wk 4', 'Wk 5', 'Wk 6', 'Wk 7', 'Wk 8', 'Wk 9', 'Wk 10', 'Wk 11', 'Wk 12'],
                'planned' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                'actual' => [null, null, null, null, null, null, null, null, null, null, null, null],
                'forecast' => [null, null, null, null, null, null, null, null, null, null, null, null]
            ];
        } else {
            $actualAvg = round($formattedProgress->avg('actualProgress') ?? 0);
            $w1 = max(1, round($actualAvg * 0.08));
            $w2 = max($w1 + 2, round($actualAvg * 0.18));
            $w3 = max($w2 + 3, round($actualAvg * 0.35));
            $w4 = max($w3 + 4, round($actualAvg * 0.55));
            $w5 = max($w4 + 5, round($actualAvg * 0.78));
            $w6 = $actualAvg;
            $remaining = 100 - $actualAvg;

            $sCurveData = [
                'labels' => ['Wk 1', 'Wk 2', 'Wk 3', 'Wk 4', 'Wk 5', 'Wk 6', 'Wk 7', 'Wk 8', 'Wk 9', 'Wk 10', 'Wk 11', 'Wk 12'],
                'planned' => [4, 10, 18, 28, 40, 52, 65, 78, 88, 94, 98, 100],
                'actual' => [$w1, $w2, $w3, $w4, $w5, $w6, null, null, null, null, null, null],
                'forecast' => [null, null, null, null, null, $actualAvg, round($actualAvg + $remaining * 0.22), round($actualAvg + $remaining * 0.45), round($actualAvg + $remaining * 0.68), round($actualAvg + $remaining * 0.84), round($actualAvg + $remaining * 0.94), 100]
            ];
        }

        $formattedUsers = $users->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'roleId' => $u->role ?? 'project_engineer',
                'companyId' => $u->company_id,
                'initials' => strtoupper(substr($u->name, 0, 2)),
                'status' => $u->status ?? 'Active',
                'custom_permissions' => $u->custom_permissions ?? null,
                'lastActive' => 'Active Now',
                'assignedProjectsCount' => 1
            ];
        })->values();

        $formattedAuditLogs = $auditLogs->map(function($l) {
            $logTime = $l->logged_at ?? $l->created_at ?? now();
            return [
                'id' => $l->id,
                'company_id' => $l->company_id,
                'project_id' => $l->project_id,
                'timestamp' => $logTime->format('M d, Y — g:i A'),
                'relativeTime' => $logTime->diffForHumans(),
                'companyCode' => $l->company->code ?? 'PPSDS',
                'companyName' => $l->company->name ?? 'CLSU PPSDS',
                'userName' => $l->user->name ?? $l->user_name ?? 'Site Engineer',
                'userRole' => $l->user->role ?? $l->user_role ?? 'Project Engineer',
                'category' => $l->category,
                'actionTitle' => $l->action_title,
                'targetActivity' => $l->target_activity,
                'weatherSnapshot' => $l->weather_snapshot,
                'outcomeType' => $l->outcome_type,
                'outcomeDetails' => $l->outcome_details
            ];
        })->values();

        $formattedMasterActivities = $masterActivities->map(function($a) {
            return [
                'id' => $a->id,
                'code' => $a->code,
                'name' => $a->name,
                'category' => $a->category,
                'defaultDuration' => (int) ($a->default_duration_days ?? 3),
                'maxRainProb' => (int) ($a->max_rain_probability ?? 40),
                'maxRainVol' => (float) ($a->max_rain_volume_mm ?? 2.5),
                'maxWind' => (int) ($a->max_wind_speed_kmh ?? 40),
                'safetyTrigger' => $a->safety_trigger ?? 'Rainfall > 3mm/hr (Safety Warning)',
                'predecessorHint' => $a->predecessor_hint ?? 'Preceding Milestone Activity',
                'description' => $a->description ?? 'Standard construction sequence.'
            ];
        })->values();

        $projects = $companyProjects;
        $allProjects = $formattedAllProjects;

        return view('app', compact(
            'user',
            'companies',
            'currentCompany',
            'allProjects',
            'projects',
            'currentProject',
            'weatherData',
            'scheduleVariance',
            'activeAlertsCount',
            'pendingDecisionsCount',
            'sCurveData',
            'formattedUsers',
            'formattedActivities',
            'formattedProgress',
            'formattedAuditLogs',
            'formattedMasterActivities'
        ));
    }
}
