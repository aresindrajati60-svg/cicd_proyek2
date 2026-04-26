<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminWisata;
use Illuminate\Support\Facades\Hash;

class AdminWisataSeeder extends Seeder
{
    public function run(): void
    {
        AdminWisata::create([
            'nama' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('666666'),
            'role' => 'admin'
        ]);
    }
}