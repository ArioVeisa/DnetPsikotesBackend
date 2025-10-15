<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Test;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    Public function run(): void
    {
        // Disable foreign key checks temporarily
        // // Clear existing records
        Test::truncate();
        // // Foreign key checks enabled

        $tests = [
            [
                'name' => 'Tes Rekrutmen Manager',
                'target_position' => 'Manager',
                'icon_path' => '/icons/manager.png',
                'started_date' => now()->addDays(5),
                'access_type' => 'Public',

            ],
            [
                'name' => 'Tes Rekrutmen Staff',
                'target_position' => 'Staff',
                'icon_path' => '/icons/staff.png',
                'started_date' => now()->addDays(5),
                'access_type' => 'Public',
            ],
            [
                'name' => 'Tes Rekrutmen Supervisor',
                'target_position' => 'Supervisor',
                'icon_path' => '/icons/supervisor.png',
                'started_date' => now()->addDays(10),
                'access_type' => 'Public',
            ],
            [
                'name' => 'Tes Rekrutmen HRD',
                'target_position' => 'HRD',
                'icon_path' => '/icons/hrd.png',
                'started_date' => now()->addDays(3),
                'access_type' => 'Public',
            ],
            [
                'name' => 'Tes Rekrutmen IT',
                'target_position' => 'IT',
                'icon_path' => '/icons/it.png',
                'started_date' => now()->addDays(14),
                'access_type' => 'Public',
            ],
            [
                'name' => 'Tes Rekrutmen Finance',
                'target_position' => 'Finance',
                'icon_path' => '/icons/finance.png',
                'started_date' => now()->addDays(21),
                'access_type' => 'Public',
            ],
            [
                'name' => 'Tes Rekrutmen Marketing',
                'target_position' => 'Marketing',
                'icon_path' => '/icons/marketing.png',
                'started_date' => now()->addDays(28),
                'access_type' => 'Public',
            ],
            [
                'name' => 'Tes Rekrutmen Sales',
                'target_position' => 'Sales',
                'icon_path' => '/icons/sales.png',
                'started_date' => now()->addDays(35),
                'access_type' => 'Public',
            ],
        ];

        foreach ($tests as $test) {
            Test::create($test);
        }
    }
}
