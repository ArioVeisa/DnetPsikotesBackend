<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidate;
use Illuminate\Support\Facades\DB;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Candidate::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Candidate::create([
            'name' => 'Budi Santoso',
            'nik' => '3201010101010001',
            'phone_number' => '081234567890',
            'email' => 'budi.santoso@example.com',
            'position' => 'Staff',
            'birth_date'   => '1990-05-10',
            'gender' => 'male',
            'department' => 'HRD'
        ]);

        Candidate::create([
            'name' => 'Citra Lestari',
            'nik' => '3201010101010002',
            'phone_number' => '081234567891',
            'email' => 'citra.lestari@example.com',
            'position' => 'Staff',
            'birth_date'   => '1990-05-10',
            'gender' => 'famale',
            'department' => 'IT'
        ]);

        Candidate::create([
            'name' => 'Doni Firmansyah',
            'nik' => '3201010101010003',
            'phone_number' => '081234567892',
            'email' => 'doni.firmansyah@example.com',
            'position' => 'Manager',
            'birth_date'   => '1990-05-10',
            'gender' => 'famale',
            'department' => 'HRD'
        ]);
    }
}
