<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Project;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $ppsdsCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ppsdsCompany = Company::create([
            'name' => 'CLSU - Physical Plant & Site Development Services (PPSDS)',
            'code' => 'PPSDS',
            'contact_person' => 'Engr. University Director',
            'contact_email' => 'ppsds@clsu.edu.ph',
            'contact_phone' => '(044) 456-7200',
            'address' => 'CLSU Main Campus, Science City of Muñoz, Nueva Ecija',
            'status' => 'Active',
        ]);

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'company_id' => $this->ppsdsCompany->id,
            'status' => 'Active',
        ]);
    }

    public function test_can_list_all_companies(): void
    {
        Company::create([
            'name' => 'Megawide Construction Corp.',
            'code' => 'MEGAWIDE',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->adminUser)->getJson(route('companies.index'));

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ])
                 ->assertJsonFragment(['code' => 'MEGAWIDE'])
                 ->assertJsonFragment(['code' => 'PPSDS']);
    }

    public function test_can_create_contractor_company(): void
    {
        $payload = [
            'name' => 'First Balfour Construction',
            'code' => 'BALFOUR',
            'contact_person' => 'Engr. Juan Dela Cruz',
            'contact_email' => 'contact@firstbalfour.com',
            'contact_phone' => '09171234567',
            'address' => 'KM 148 Maharlika Highway, Muñoz, Nueva Ecija',
            'status' => 'Active',
        ];

        $response = $this->actingAs($this->adminUser)->postJson(route('companies.store'), $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Contractor company registered successfully',
                 ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'First Balfour Construction',
            'code' => 'BALFOUR',
            'contact_person' => 'Engr. Juan Dela Cruz',
        ]);
    }

    public function test_validates_required_fields_when_creating_company(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('companies.store'), [
            'name' => '',
            'code' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'code']);
    }

    public function test_prevents_duplicate_company_code(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('companies.store'), [
            'name' => 'Another PPSDS Division',
            'code' => 'PPSDS', // Already taken
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code']);
    }

    public function test_can_update_company_profile(): void
    {
        $contractor = Company::create([
            'name' => 'EEI Corporation',
            'code' => 'EEI',
            'contact_person' => 'Old Contact',
            'status' => 'Active',
        ]);

        $updatePayload = [
            'name' => 'EEI Corporation Philippines',
            'code' => 'EEI-PH',
            'contact_person' => 'Engr. Pedro Santos',
            'contact_email' => 'eei.projects@eei.com.ph',
            'contact_phone' => '09189876543',
            'address' => 'Brgy. Bantug, Science City of Muñoz, Nueva Ecija',
            'status' => 'Active',
        ];

        $response = $this->actingAs($this->adminUser)->putJson(route('companies.update', $contractor), $updatePayload);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Contractor company profile updated successfully',
                 ]);

        $this->assertDatabaseHas('companies', [
            'id' => $contractor->id,
            'name' => 'EEI Corporation Philippines',
            'code' => 'EEI-PH',
            'contact_person' => 'Engr. Pedro Santos',
        ]);
    }

    public function test_prevents_deleting_primary_ppsds_company(): void
    {
        $response = $this->actingAs($this->adminUser)->deleteJson(route('companies.destroy', $this->ppsdsCompany));

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'message' => 'The primary university physical plant department (PPSDS) cannot be removed.',
                 ]);

        $this->assertDatabaseHas('companies', [
            'id' => $this->ppsdsCompany->id,
            'code' => 'PPSDS',
        ]);
    }

    public function test_can_delete_contractor_company_and_reassigns_projects_to_ppsds(): void
    {
        $contractor = Company::create([
            'name' => 'Temporary Contractor Co.',
            'code' => 'TEMP-CO',
            'status' => 'Active',
        ]);

        $project = Project::create([
            'company_id' => $contractor->id,
            'code' => 'CLSU-TMP-01',
            'name' => 'Temporary Facility Hall',
            'location' => 'CLSU Campus',
            'latitude' => 15.7423,
            'longitude' => 120.9405,
            'status' => 'active',
            'budget' => 5000000,
            'start_date' => '2026-01-01',
            'target_completion' => '2026-12-31',
        ]);

        $response = $this->actingAs($this->adminUser)->deleteJson(route('companies.destroy', $contractor));

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Contractor organization deleted successfully',
                 ]);

        $this->assertDatabaseMissing('companies', [
            'id' => $contractor->id,
        ]);

        // Reassigned to PPSDS
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'company_id' => $this->ppsdsCompany->id,
        ]);
    }
}
