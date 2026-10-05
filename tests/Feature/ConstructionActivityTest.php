<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Project;
use App\Models\MasterActivity;
use App\Models\Schedule;

class ConstructionActivityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup base organization, user and project
        $this->company = Company::create([
            'name' => 'CLSU - Physical Plant & Site Development Services',
            'code' => 'PPSDS',
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'Engr. Juan Dela Cruz',
            'email' => 'engineer@vertical.ph',
            'password' => bcrypt('password123'),
            'role' => 'project_engineer',
            'status' => 'Active',
        ]);

        $this->project = Project::create([
            'company_id' => $this->company->id,
            'name' => 'CLSU College of Engineering Research Complex',
            'code' => 'CLSU-ENG-2026',
            'location' => 'CLSU Campus, Science City of Muñoz, Nueva Ecija',
            'latitude' => 15.7144,
            'longitude' => 120.9307,
            'storeys' => 5,
            'duration_weeks' => 12,
            'status' => 'In Progress',
        ]);
    }

    public function test_can_list_all_predefined_construction_activities(): void
    {
        MasterActivity::create([
            'code' => 'ACT-STR-01',
            'name' => 'Structural Concrete Pouring',
            'category' => 'Structural',
            'default_duration_days' => 2,
            'max_rain_probability' => 40,
            'max_rain_volume_mm' => 2.5,
            'max_wind_speed_kmh' => 40,
            'safety_trigger' => 'Rainfall > 3mm/hr',
            'predecessor_hint' => 'Rebar & Formworks',
            'description' => 'Continuous monolithic slab pour',
        ]);

        $response = $this->actingAs($this->user)->getJson('/master-activities');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'code' => 'ACT-STR-01',
                'name' => 'Structural Concrete Pouring',
            ]);
    }

    public function test_can_create_a_new_predefined_construction_activity(): void
    {
        $payload = [
            'name' => 'Roof Steel Truss Assembly',
            'code' => 'ACT-STR-05',
            'category' => 'Structural',
            'default_duration_days' => 4,
            'max_rain_probability' => 35,
            'max_rain_volume_mm' => 1.5,
            'max_wind_speed_kmh' => 25,
            'safety_trigger' => 'Wind Gusts > 30 km/h (High Elevation Risk)',
            'predecessor_hint' => 'Column Concrete Pouring',
            'description' => 'Heavy steel truss lifting and welding for roof framing.',
        ];

        $response = $this->actingAs($this->user)->postJson('/master-activities', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Master activity created successfully',
            ]);

        $this->assertDatabaseHas('master_activities', [
            'code' => 'ACT-STR-05',
            'name' => 'Roof Steel Truss Assembly',
            'max_rain_probability' => 35,
            'max_wind_speed_kmh' => 25,
        ]);

        // Verify Audit Log entry created
        $this->assertDatabaseHas('audit_logs', [
            'target_activity' => 'Catalog: Roof Steel Truss Assembly',
            'outcome_type' => 'Activity Created',
        ]);
    }

    public function test_validates_required_fields_when_creating_an_activity(): void
    {
        $response = $this->actingAs($this->user)->postJson('/master-activities', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'code', 'category', 'default_duration_days', 'max_rain_probability', 'max_wind_speed_kmh']);
    }

    public function test_can_update_an_existing_predefined_activity(): void
    {
        $activity = MasterActivity::create([
            'code' => 'ACT-ENC-01',
            'name' => 'Exterior Waterproofing',
            'category' => 'Enclosure',
            'default_duration_days' => 2,
            'max_rain_probability' => 30,
            'max_rain_volume_mm' => 1.0,
            'max_wind_speed_kmh' => 25,
            'safety_trigger' => 'Rainfall > 1mm/hr',
            'predecessor_hint' => 'Wall Plastering',
            'description' => 'Waterproofing membrane application',
        ]);

        $updatePayload = [
            'name' => 'Exterior Waterproofing & Liquid Membrane Coating',
            'code' => 'ACT-ENC-01',
            'category' => 'Enclosure',
            'default_duration_days' => 3,
            'max_rain_probability' => 25,
            'max_rain_volume_mm' => 0.5,
            'max_wind_speed_kmh' => 20,
            'safety_trigger' => 'Humidity > 85% & Rain > 0.5mm/hr',
            'predecessor_hint' => 'Wall Plastering Inspection',
            'description' => 'Updated dual layer waterproofing application',
        ];

        $response = $this->actingAs($this->user)->putJson("/master-activities/{$activity->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Master activity updated successfully',
            ]);

        $this->assertDatabaseHas('master_activities', [
            'id' => $activity->id,
            'name' => 'Exterior Waterproofing & Liquid Membrane Coating',
            'default_duration_days' => 3,
            'max_rain_probability' => 25,
        ]);
    }

    public function test_can_delete_an_activity_when_multiple_exist(): void
    {
        $act1 = MasterActivity::create([
            'code' => 'ACT-01',
            'name' => 'Activity 1',
            'category' => 'Structural',
            'default_duration_days' => 2,
            'max_rain_probability' => 40,
            'max_wind_speed_kmh' => 40,
        ]);

        $act2 = MasterActivity::create([
            'code' => 'ACT-02',
            'name' => 'Activity 2',
            'category' => 'Structural',
            'default_duration_days' => 3,
            'max_rain_probability' => 50,
            'max_wind_speed_kmh' => 35,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/master-activities/{$act1->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Master activity removed successfully',
            ]);

        $this->assertDatabaseMissing('master_activities', [
            'id' => $act1->id,
        ]);
    }

    public function test_prevents_deleting_the_last_remaining_activity(): void
    {
        $singleAct = MasterActivity::create([
            'code' => 'ACT-ONLY',
            'name' => 'Only Activity',
            'category' => 'Structural',
            'default_duration_days' => 2,
            'max_rain_probability' => 40,
            'max_wind_speed_kmh' => 40,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/master-activities/{$singleAct->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'At least one predefined construction activity must remain in the master catalog.',
            ]);

        $this->assertDatabaseHas('master_activities', [
            'id' => $singleAct->id,
        ]);
    }

    public function test_can_schedule_an_activity_to_the_gantt_chart(): void
    {
        $master = MasterActivity::create([
            'code' => 'ACT-TEST',
            'name' => 'Rebar Installation',
            'category' => 'Structural',
            'default_duration_days' => 3,
            'max_rain_probability' => 60,
            'max_wind_speed_kmh' => 45,
        ]);

        $schedulePayload = [
            'project_id' => $this->project->id,
            'master_activity_id' => $master->id,
            'name' => 'Rebar Installation (Level 3)',
            'duration_days' => 3,
            'start_date' => '2026-10-01',
            'weather_rule_text' => 'Rain < 60%, Wind < 45km/h',
        ];

        $response = $this->actingAs($this->user)->postJson('/schedules', $schedulePayload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Activity successfully added to Gantt schedule.',
            ]);

        $this->assertDatabaseHas('schedules', [
            'project_id' => $this->project->id,
            'name' => 'Rebar Installation (Level 3)',
            'duration_days' => 3,
            'status' => 'Scheduled',
        ]);
    }
}
