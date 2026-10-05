<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'CLSU - PPSDS',
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
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'engineer@vertical.ph',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'engineer@vertical.ph',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_user_can_logout_via_post(): void
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_user_can_logout_via_get(): void
    {
        $response = $this->actingAs($this->user)->get('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
