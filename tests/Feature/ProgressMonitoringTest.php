<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Project;
use App\Models\Schedule;
use App\Models\MasterActivity;
use App\Models\ProgressRecord;

class ProgressMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $user;
    protected $project;
    protected $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'CLSU - Physical Plant & Site Development Services (PPSDS)',
            'code' => 'PPSDS',
        ]);

        $this->user = User::factory()->create([
            'role' => 'site_supervisor',
            'company_id' => $this->company->id,
            'status' => 'Active',
        ]);

        $this->project = Project::create([
            'company_id' => $this->company->id,
            'name' => 'College of Engineering Complex',
            'code' => 'CLSU-ENG-01',
            'location' => 'CLSU Campus, Science City of Muñoz',
            'status' => 'In Progress',
        ]);

        $masterAct = MasterActivity::create([
            'code' => 'ACT-01',
            'name' => 'Structural Concrete Pouring',
            'category' => 'Structural',
            'default_duration_days' => 3,
        ]);

        $this->schedule = Schedule::create([
            'project_id' => $this->project->id,
            'master_activity_id' => $masterAct->id,
            'name' => 'Rebar Installation & Formworks',
            'duration_days' => 3,
            'baseline_start_date' => '2026-09-27',
            'baseline_end_date' => '2026-09-29',
            'current_start_date' => '2026-09-27',
            'current_end_date' => '2026-09-29',
            'status' => 'In Progress',
        ]);

        ProgressRecord::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->user->id,
            'record_date' => '2026-09-27',
            'planned_progress' => 85,
            'actual_progress' => 85,
            'variance' => 0,
            'status' => 'On Track',
        ]);
    }

    public function test_can_update_daily_progress_accomplishment(): void
    {
        $payload = [
            'schedule_id' => $this->schedule->id,
            'actual_progress' => 95,
            'notes' => 'Accomplishment submitted on time'
        ];

        $response = $this->actingAs($this->user)->postJson(route('progress.store'), $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);

        $this->assertDatabaseHas('progress_records', [
            'schedule_id' => $this->schedule->id,
            'actual_progress' => 95,
        ]);

        // Verify that visiting dashboard persists the updated actual progress (95%)
        $dashboardResponse = $this->actingAs($this->user)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('"actualProgress":95', false);
    }
}
