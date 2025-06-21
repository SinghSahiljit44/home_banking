<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SecurityQuestion;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@homebanking.com',
            'password' => Hash::make('admin123'),
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'phone' => '+1234567890',
            'address' => 'Bank Headquarters',
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        SecurityQuestion::create([
            'user_id' => $admin->id,
            'question' => 'What is your mother\'s maiden name?',
            'answer_hash' => Hash::make('admin'),
        ]);
    }
}