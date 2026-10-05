<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Company $ppsds;
    protected Company $contractorCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ppsds = Company::create([
            'name' => 'CLSU - Physical Plant & Site Development Services',
            'code' => 'PPSDS',
        ]);

        $this->contractorCompany = Company::create([
            'name' => 'Megawide Construction Corp.',
            'code' => 'MEGA',
        ]);

        $this->admin = User::create([
            'company_id' => $this->ppsds->id,
            'name' => 'Atty. Sofia Reyes',
            'email' => 'admin@vertical.ph',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'Active',
        ]);
    }

    public function test_can_list_all_users_with_company(): void
    {
        User::create([
            'company_id' => $this->contractorCompany->id,
            'name' => 'Carlos Mendoza',
            'email' => 'supervisor@vertical.ph',
            'password' => bcrypt('password123'),
            'role' => 'site_supervisor',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/users');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'email' => 'supervisor@vertical.ph',
                'name' => 'Carlos Mendoza',
                'role' => 'site_supervisor',
            ]);
    }

    public function test_can_filter_users_by_company(): void
    {
        $supervisor = User::create([
            'company_id' => $this->contractorCompany->id,
            'name' => 'Carlos Mendoza',
            'email' => 'supervisor@vertical.ph',
            'password' => bcrypt('password123'),
            'role' => 'site_supervisor',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/users?company_id=' . $this->contractorCompany->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'email' => 'supervisor@vertical.ph',
            ])
            ->assertJsonMissing([
                'email' => 'admin@vertical.ph',
            ]);
    }

    public function test_can_create_a_new_user_with_role(): void
    {
        $payload = [
            'company_id' => $this->contractorCompany->id,
            'name' => 'Engr. Ramon Santos',
            'email' => 'ramon.santos@megawide.ph',
            'role' => 'site_supervisor',
            'password' => 'password123',
        ];

        $response = $this->actingAs($this->admin)->postJson('/users', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Engr. Ramon Santos',
            'email' => 'ramon.santos@megawide.ph',
            'role' => 'site_supervisor',
            'company_id' => $this->contractorCompany->id,
            'status' => 'Active',
        ]);

        // Verify Audit Log entry created for user registration
        $this->assertDatabaseHas('audit_logs', [
            'target_activity' => 'User: Engr. Ramon Santos',
            'outcome_type' => 'User Created',
        ]);
    }

    public function test_validates_required_fields_when_creating_user(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_id', 'name', 'email', 'role']);
    }

    public function test_prevents_creating_user_with_duplicate_email(): void
    {
        $payload = [
            'company_id' => $this->contractorCompany->id,
            'name' => 'Duplicate Admin',
            'email' => 'admin@vertical.ph', // Duplicate
            'role' => 'project_engineer',
            'password' => 'password123',
        ];

        $response = $this->actingAs($this->admin)->postJson('/users', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_update_an_existing_user(): void
    {
        $user = User::create([
            'company_id' => $this->contractorCompany->id,
            'name' => 'Juan Site Engineer',
            'email' => 'juan.site@megawide.ph',
            'password' => bcrypt('password123'),
            'role' => 'site_supervisor',
            'status' => 'Active',
        ]);

        $updatePayload = [
            'company_id' => $this->ppsds->id, // Reassigned to PPSDS
            'name' => 'Engr. Juan Dela Cruz (Promoted)',
            'email' => 'juan.delacruz@clsu-ppsds.edu.ph',
            'role' => 'project_engineer',
            'status' => 'Active',
        ];

        $response = $this->actingAs($this->admin)->putJson("/users/{$user->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Engr. Juan Dela Cruz (Promoted)',
            'email' => 'juan.delacruz@clsu-ppsds.edu.ph',
            'role' => 'project_engineer',
            'company_id' => $this->ppsds->id,
        ]);
    }

    public function test_can_delete_a_user(): void
    {
        $targetUser = User::create([
            'company_id' => $this->contractorCompany->id,
            'name' => 'Temporary Contractor Staff',
            'email' => 'temp.staff@megawide.ph',
            'password' => bcrypt('password123'),
            'role' => 'contractor',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/users/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User removed successfully',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);

        // Verify Audit Log entry created for user removal
        $this->assertDatabaseHas('audit_logs', [
            'target_activity' => 'User: Temporary Contractor Staff',
            'outcome_type' => 'User Deleted',
        ]);
    }

    public function test_prevents_deleting_currently_authenticated_user(): void
    {
        // Try to delete the active admin session user
        $response = $this->actingAs($this->admin)->deleteJson("/users/{$this->admin->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'You cannot delete the currently authenticated session user.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }

    public function test_prevents_deleting_the_last_remaining_user(): void
    {
        // Delete all except one
        $singleUser = User::first();

        // Acting as singleUser but attempting to delete
        $response = $this->actingAs($singleUser)->deleteJson("/users/{$singleUser->id}");

        $response->assertStatus(422);

        $this->assertDatabaseHas('users', [
            'id' => $singleUser->id,
        ]);
    }

    public function test_can_create_user_with_custom_permissions(): void
    {
        $customPerms = [
            'tab_dashboard' => true,
            'tab_scheduling' => true,
            'tab_projects' => true,
            'tab_progress' => true,
            'tab_activity_library' => true,
            'tab_audit' => true,
            'tab_weather_config' => false,
            'tab_users' => false,
            'action_reschedule' => true,
            'action_continue_same_day' => true,
            'action_edit_progress' => true,
            'action_export_audit' => true,
            'action_manage_activities' => false,
            'action_manage_projects' => false,
            'action_manage_users' => false,
        ];

        $payload = [
            'company_id' => $this->contractorCompany->id,
            'name' => 'Engr. Custom Access Supervisor',
            'email' => 'custom.supervisor@megawide.ph',
            'role' => 'site_supervisor',
            'password' => 'password123',
            'custom_permissions' => $customPerms,
        ];

        $response = $this->actingAs($this->admin)->postJson('/users', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully',
            ]);

        $created = User::where('email', 'custom.supervisor@megawide.ph')->first();
        $this->assertNotNull($created);
        $this->assertEquals('site_supervisor', $created->role);
        $this->assertEquals(true, $created->custom_permissions['tab_audit']);
        $this->assertEquals(true, $created->custom_permissions['tab_activity_library']);
        $this->assertEquals(false, $created->custom_permissions['action_manage_users']);
    }

    public function test_can_update_user_custom_permissions(): void
    {
        $user = User::create([
            'company_id' => $this->contractorCompany->id,
            'name' => 'Field Engineer Alpha',
            'email' => 'field.alpha@megawide.ph',
            'password' => bcrypt('password123'),
            'role' => 'site_supervisor',
            'status' => 'Active',
            'custom_permissions' => [
                'tab_audit' => false,
            ],
        ]);

        $updatePayload = [
            'company_id' => $this->contractorCompany->id,
            'name' => 'Field Engineer Alpha (Granted Audit & Activity Access)',
            'email' => 'field.alpha@megawide.ph',
            'role' => 'site_supervisor',
            'status' => 'Active',
            'custom_permissions' => [
                'tab_audit' => true,
                'tab_activity_library' => true,
                'action_export_audit' => true,
            ],
        ];

        $response = $this->actingAs($this->admin)->putJson("/users/{$user->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User updated successfully',
            ]);

        $user->refresh();
        $this->assertEquals(true, $user->custom_permissions['tab_audit']);
        $this->assertEquals(true, $user->custom_permissions['tab_activity_library']);
        $this->assertEquals(true, $user->custom_permissions['action_export_audit']);
    }
}

