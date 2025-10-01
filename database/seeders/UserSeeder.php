<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        User::create([
            'name' => 'Super Admin DWP',
            'email' => 'superadmin@dwp.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'department' => 'Management' // <-- Pastikan ini ada
        ]);

        User::create([
            'name' => 'Admin HRD DWP',
            'email' => 'admin.hrd@dwp.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'department' => 'HRD' // <-- Pastikan ini ada
        ]);

        User::create([
            'name' => 'Kandidat Uji Coba',
            'email' => 'kandidat@dwp.com',
            'password' => Hash::make('password123'),
            'role' => 'kandidat',
            'department' => null // Kandidat tidak punya departemen
        ]);
    }
}