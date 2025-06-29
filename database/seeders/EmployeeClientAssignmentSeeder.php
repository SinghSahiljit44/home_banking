<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EmployeeClientAssignment;

class EmployeeClientAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::where('role', 'employee')->get();
        $clients = User::where('role', 'client')->get();
        $admin = User::where('role', 'admin')->first();

        if ($employees->isEmpty() || $clients->isEmpty() || !$admin) {
            $this->command->info('Skipping EmployeeClientAssignment seeder - missing users');
            return;
        }

        // Distribuisci i clienti tra gli employee in modo equilibrato
        $clientsPerEmployee = ceil($clients->count() / $employees->count());
        
        $clientIndex = 0;
        foreach ($employees as $employee) {
            $assignedCount = 0;
            
            while ($assignedCount < $clientsPerEmployee && $clientIndex < $clients->count()) {
                $client = $clients[$clientIndex];
                
                // Verifica che l'assegnazione non esista già
                $exists = EmployeeClientAssignment::where('employee_id', $employee->id)
                    ->where('client_id', $client->id)
                    ->exists();
                
                if (!$exists) {
                    EmployeeClientAssignment::create([
                        'employee_id' => $employee->id,
                        'client_id' => $client->id,
                        'assigned_by' => $admin->id,
                        'notes' => "Assegnazione automatica - Cliente assegnato a {$employee->full_name}",
                        'is_active' => true,
                        'assigned_at' => now()->subDays(rand(1, 30)),
                    ]);
                    
                    $this->command->info("Cliente {$client->full_name} assegnato a {$employee->full_name}");
                }
                
                $clientIndex++;
                $assignedCount++;
            }
        }
        
        $this->command->info('Assegnazioni Employee-Client completate!');
    }
}