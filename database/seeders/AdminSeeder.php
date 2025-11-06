<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete any existing admin accounts to ensure only one admin exists
        Admin::where('role', 'admin')->delete();

        // Create the one and only admin account
        Admin::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'), // Change this password after first login!
            'role' => 'admin',
            'is_seeded' => true, // Mark as seeded so it can't be deleted
        ]);

        $this->command->info('Admin account created successfully!');
        $this->command->warn('Default credentials:');
        $this->command->warn('Username: admin');
        $this->command->warn('Password: admin123');
        $this->command->error('⚠️  IMPORTANT: Change this password immediately after first login!');
    }
}