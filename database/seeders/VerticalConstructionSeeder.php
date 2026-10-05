<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;
use App\Models\User;
use App\Models\MasterActivity;

class VerticalConstructionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Organizations & Contractor Companies
        $ppsds = Company::create([
            'name' => 'CLSU - Physical Plant & Site Development Services',
            'code' => 'PPSDS',
            'contact_person' => 'Atty. Sofia Reyes (PPSDS Director)',
            'contact_email' => 'ppsds@clsu.edu.ph',
            'contact_phone' => '+63 44 456 0107',
            'address' => 'PPSDS Bldg., CLSU Main Campus, Science City of Muñoz, Nueva Ecija',
            'status' => 'Active',
            'logo_url' => null
        ]);

        // 2. Essential User Accounts (All linked to host PPSDS)
        User::create([
            'company_id' => $ppsds->id,
            'name' => 'Atty. Sofia Reyes',
            'email' => 'admin@vertical.ph',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'initials' => 'SR',
            'status' => 'Active',
        ]);

        User::create([
            'company_id' => $ppsds->id,
            'name' => 'Engr. Juan Dela Cruz',
            'email' => 'engineer@vertical.ph',
            'password' => Hash::make('password123'),
            'role' => 'project_engineer',
            'initials' => 'JD',
            'status' => 'Active',
        ]);

        User::create([
            'company_id' => $ppsds->id,
            'name' => 'Carlos Mendoza',
            'email' => 'supervisor@vertical.ph',
            'password' => Hash::make('password123'),
            'role' => 'site_supervisor',
            'initials' => 'CM',
            'status' => 'Active',
        ]);

        User::create([
            'company_id' => $ppsds->id,
            'name' => 'Engr. Ramon Santos',
            'email' => 'contractor@vertical.ph',
            'password' => Hash::make('password123'),
            'role' => 'contractor',
            'initials' => 'RS',
            'status' => 'Active',
        ]);
    }
}
