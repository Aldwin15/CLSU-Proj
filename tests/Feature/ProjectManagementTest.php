<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Project;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'CLSU - Physical Plant & Site Development Services',
            'code' => 'PPSDS',
        ]);

        $this->user = User::create([
            'company_id' => $this->company->id,
            'name' => 'Atty. Sofia Reyes',
            'email' => 'admin@vertical.ph',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'Active',
        ]);
    }

    public function test_can_list_all_projects_for_a_company(): void
    {
        Project::create([
            'company_id' => $this->company->id,
            'name' => 'CLSU College of Engineering Complex',
            'code' => 'CLSU-ENG-2026',
            'location' => 'CLSU Campus, Science City of Muñoz, Nueva Ecija',
            'latitude' => 15.7144,
            'longitude' => 120.9307,
            'storeys' => 5,
            'duration_weeks' => 12,
            'status' => 'In Progress',
        ]);

        $response = $this->actingAs($this->user)->getJson('/projects?company_id=' . $this->company->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'code' => 'CLSU-ENG-2026',
                'name' => 'CLSU College of Engineering Complex',
            ]);
    }

    public function test_can_create_a_new_project_workspace(): void
    {
        $payload = [
            'company_id' => $this->company->id,
            'name' => 'New University Student Dormitory Building',
            'code' => 'CLSU-DORM-2026',
            'location' => 'CLSU Campus, Science City of Muñoz, Nueva Ecija',
            'latitude' => 15.7144,
            'longitude' => 120.9307,
            'total_storeys' => 4,
            'baseline_duration_weeks' => 10,
        ];

        $response = $this->actingAs($this->user)->postJson('/projects', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project created successfully',
            ]);

        $this->assertDatabaseHas('projects', [
            'company_id' => $this->company->id,
            'code' => 'CLSU-DORM-2026',
            'name' => 'New University Student Dormitory Building',
            'storeys' => 4,
            'duration_weeks' => 10,
        ]);

        // Verify Audit Log entry created for the new project workspace
        $this->assertDatabaseHas('audit_logs', [
            'target_activity' => 'New University Student Dormitory Building',
            'outcome_type' => 'Schedule Approved',
        ]);
    }

    public function test_validates_required_fields_when_creating_a_project(): void
    {
        $response = $this->actingAs($this->user)->postJson('/projects', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_id', 'name', 'code', 'location']);
    }

    public function test_prevents_creating_project_with_duplicate_code(): void
    {
        Project::create([
            'company_id' => $this->company->id,
            'name' => 'Existing Building',
            'code' => 'CLSU-EXIST-2026',
            'location' => 'CLSU Campus, Science City of Muñoz, Nueva Ecija',
            'storeys' => 3,
            'duration_weeks' => 8,
        ]);

        $payload = [
            'company_id' => $this->company->id,
            'name' => 'Another Building',
            'code' => 'CLSU-EXIST-2026', // Duplicate code
            'location' => 'CLSU Campus, Science City of Muñoz, Nueva Ecija',
            'total_storeys' => 4,
            'baseline_duration_weeks' => 10,
        ];

        $response = $this->actingAs($this->user)->postJson('/projects', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_can_update_an_existing_project(): void
    {
        $project = Project::create([
            'company_id' => $this->company->id,
            'name' => 'Central Science Complex',
            'code' => 'CLSU-AGRI-2026',
            'location' => 'CLSU Campus, Science City of Muñoz, Nueva Ecija',
            'storeys' => 5,
            'duration_weeks' => 14,
            'status' => 'In Progress',
        ]);

        $updatePayload = [
            'name' => 'Central Science & Agri-Technology Complex (Phase 2)',
            'code' => 'CLSU-AGRI-2026',
            'location' => 'CLSU Campus, Science City of Muñoz, Nueva Ecija (Zone B)',
            'latitude' => 15.7144,
            'longitude' => 120.9307,
            'total_storeys' => 6,
            'baseline_duration_weeks' => 16,
        ];

        $response = $this->actingAs($this->user)->putJson("/projects/{$project->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project updated successfully',
            ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Central Science & Agri-Technology Complex (Phase 2)',
            'location' => 'CLSU Campus, Science City of Muñoz, Nueva Ecija (Zone B)',
            'storeys' => 6,
            'duration_weeks' => 16,
        ]);
    }

    public function test_can_delete_a_project_when_multiple_exist(): void
    {
        $p1 = Project::create([
            'company_id' => $this->company->id,
            'name' => 'Project 1',
            'code' => 'PRJ-01',
            'location' => 'CLSU Campus, Science City of Muñoz',
            'storeys' => 3,
            'duration_weeks' => 8,
        ]);

        $p2 = Project::create([
            'company_id' => $this->company->id,
            'name' => 'Project 2',
            'code' => 'PRJ-02',
            'location' => 'CLSU Campus, Science City of Muñoz',
            'storeys' => 4,
            'duration_weeks' => 10,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/projects/{$p1->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Project deleted successfully',
            ]);

        $this->assertDatabaseMissing('projects', [
            'id' => $p1->id,
        ]);
    }

    public function test_prevents_deleting_the_last_remaining_project(): void
    {
        $singleProject = Project::create([
            'company_id' => $this->company->id,
            'name' => 'Sole Active Project',
            'code' => 'PRJ-SOLE',
            'location' => 'CLSU Campus, Science City of Muñoz',
            'storeys' => 3,
            'duration_weeks' => 8,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/projects/{$singleProject->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'At least one project workspace must remain active.',
            ]);

        $this->assertDatabaseHas('projects', [
            'id' => $singleProject->id,
        ]);
    }
}
