<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuperAdmin;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        SuperAdmin::create([
            'nama' => 'Superadmin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('123456'),
            'role' => 'superadmin'
        ]);
    }
}
