<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SecurityQuestion;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Prima creiamo i ruoli base se non esistono
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $clientRole = Role::firstOrCreate(['name' => 'client']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);

        // Creiamo alcuni permessi base
        $permissions = [
            'view_dashboard',
            'manage_users',
            'view_accounts',
            'make_transfers',
            'view_transactions'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assegniamo tutti i permessi all'admin
        $adminRole->givePermissionTo(Permission::all());
        
        // Permessi base per client
        $clientRole->givePermissionTo(['view_dashboard', 'view_accounts', 'make_transfers', 'view_transactions']);
        
        // Permessi base per employee
        $employeeRole->givePermissionTo(['view_dashboard', 'view_accounts', 'view_transactions']);

        // Crea l'utente admin
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

        // Assegna il ruolo admin
        $admin->assignRole('admin');

        // Crea la security question
        SecurityQuestion::create([
            'user_id' => $admin->id,
            'question' => 'What is your mother\'s maiden name?',
            'answer_hash' => Hash::make('admin'),
        ]);

        // Crea un cliente di test
        $client = User::create([
            'username' => 'cliente1',
            'email' => 'cliente@test.com',
            'password' => Hash::make('password123'),
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'phone' => '+1234567891',
            'address' => 'Via Roma 1, Milano',
            'role' => 'client',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $client->assignRole('client');

        // Crea un dipendente di test
        $employee = User::create([
            'username' => 'dipendente1',
            'email' => 'dipendente@test.com',
            'password' => Hash::make('password123'),
            'first_name' => 'Giulia',
            'last_name' => 'Bianchi',
            'phone' => '+1234567892',
            'address' => 'Via Milano 2, Roma',
            'role' => 'employee',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $employee->assignRole('employee');
    }
}