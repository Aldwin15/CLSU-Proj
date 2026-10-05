<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $company = Company::create([
            'name' => 'CLSU - PPSDS',
            'code' => 'PPSDS',
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Engr. Juan Dela Cruz',
            'email' => 'engineer@vertical.ph',
            'password' => bcrypt('password123'),
            'role' => 'project_engineer',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }
}
