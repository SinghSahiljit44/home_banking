<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏦 Inizializzazione Sistema Bancario...');
        $this->command->info('');
        
        // Sequenza di seeding in ordine di dipendenza
        $this->call([
            // 1. Setup ruoli e permessi
            RolesAndPermissionsSeeder::class,
            
            // 2. Crea utenti di base
            AdminSeeder::class,
            
            // 3. Crea dati demo estesi
            DemoDataSeeder::class,
            
            // 4. Crea assegnazioni employee-client
            EmployeeClientAssignmentSeeder::class,
            
            // 6. Aggiungi domande di sicurezza
            SecurityQuestionSeeder::class,
        ]);
        
        $this->command->info('');
        $this->command->info('✅ Sistema Bancario inizializzato con successo!');
        $this->command->info('');
        $this->command->info('🔐 Credenziali di accesso:');
        $this->command->info('👨‍💼 ADMIN: username: admin, password: admin123');
        $this->command->info('👩‍💼 EMPLOYEE: username: emp.sportello1, password: employee123');
        $this->command->info('👤 CLIENT: username: mario.rossi, password: password123');
        $this->command->info('');
        $this->command->info('🌐 Avvia il server: php artisan serve');
    }
}
