<?php
// database/seeders/RolesAndPermissionsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crea i permessi
        $permissions = [
            // Gestione utenti
            'manage_users',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Gestione account
            'manage_accounts',
            'view_accounts',
            'create_accounts',
            'edit_accounts',
            'freeze_accounts',
            
            // Gestione transazioni
            'view_transactions',
            'create_transactions',
            'approve_transactions',
            'reject_transactions',
            'view_all_transactions',
            
            // Gestione reporting
            'view_reports',
            'generate_reports',
            'export_data',
            
            // Permessi cliente
            'view_own_account',
            'make_transfers',
            'view_own_transactions',
            'change_password',
            'manage_security_questions',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crea i ruoli e assegna i permessi
        
        // Ruolo Admin
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Ruolo Employee
        $employeeRole = Role::create(['name' => 'employee']);
        $employeeRole->givePermissionTo([
            'view_users',
            'create_users',
            'edit_users',
            'manage_accounts',
            'view_accounts',
            'create_accounts',
            'edit_accounts',
            'freeze_accounts',
            'view_transactions',
            'approve_transactions',
            'reject_transactions',
            'view_all_transactions',
            'view_reports',
            'generate_reports',
        ]);

        // Ruolo Client
        $clientRole = Role::create(['name' => 'client']);
        $clientRole->givePermissionTo([
            'view_own_account',
            'make_transfers',
            'view_own_transactions',
            'change_password',
            'manage_security_questions',
        ]);

        // Assegna ruolo admin all'utente admin esistente
        $admin = User::where('email', 'admin@homebanking.com')->first();
        if ($admin) {
            $admin->assignRole('admin');
        }
    }
}