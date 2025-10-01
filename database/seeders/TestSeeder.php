<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Test;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Test::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $tests = [
            [
                'name' => 'Tes Rekrutmen Manager',
                'target_position' => 'Manager',
                'icon_path' => '/icons/manager.png',
                'started_date' => now()->addDays(5),

            ],
            [
                'name' => 'Tes Rekrutmen Staff',
                'target_position' => 'Staff',
                'icon_path' => '/icons/staff.png',
                'started_date' => now()->addDays(5),
            ],
            [
                'name' => 'Tes Rekrutmen Supervisor',
                'target_position' => 'Supervisor',
                'icon_path' => '/icons/supervisor.png',
                'started_date' => now()->addDays(10),
            ],
            [
                'name' => 'Tes Rekrutmen HRD',
                'target_position' => 'HRD',
                'icon_path' => '/icons/hrd.png',
                'started_date' => now()->addDays(3),
            ],
            [
                'name' => 'Tes Rekrutmen IT',
                'target_position' => 'IT',
                'icon_path' => '/icons/it.png',
                'started_date' => now()->addDays(14),
            ],
            [
                'name' => 'Tes Rekrutmen Finance',
                'target_position' => 'Finance',
                'icon_path' => '/icons/finance.png',
                'started_date' => now()->addDays(21),
            ],
            [
                'name' => 'Tes Rekrutmen Marketing',
                'target_position' => 'Marketing',
                'icon_path' => '/icons/marketing.png',
                'started_date' => now()->addDays(28),
            ],
            [
                'name' => 'Tes Rekrutmen Sales',
                'target_position' => 'Sales',
                'icon_path' => '/icons/sales.png',
                'started_date' => now()->addDays(35),
            ],
        ];

        foreach ($tests as $test) {
            Test::create($test);
        }
    }
}
