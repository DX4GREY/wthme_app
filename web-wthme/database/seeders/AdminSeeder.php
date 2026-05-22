<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin WTHME',
            'email'    => 'admin@wthme.ac.id',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
            'nim'      => 'ADMIN001',
            'angkatan' => '2024',
            'divisi'   => 'Admin',
        ]);
    }
}