<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->updateOrCreate(
            ['email' => 'company@pharmaegypt.com'],
            [
                'company_name' => 'Pharma Egypt Co.',
                'password' => 'password123',
                'status' => 'active',
                'commercial_register' => 'CREG-EGY-2025-001',
            ]
        );

        Company::query()->updateOrCreate(
            ['email' => 'company@medcare.com'],
            [
                'company_name' => 'MedCare Solutions',
                'password' => 'password123',
                'status' => 'active',
                'commercial_register' => 'CREG-EGY-2025-002',
            ]
        );
    }
}
