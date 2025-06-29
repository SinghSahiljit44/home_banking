<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SecurityQuestion;
use Illuminate\Support\Facades\Hash;

class SecurityQuestionsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Trova alcuni utenti clienti per aggiungere domande di sicurezza di test
        $clients = User::where('role', 'client')->take(3)->get();
        
        $testQuestions = [
            [
                'question' => 'Qual è il nome del tuo primo animale domestico?',
                'answer' => 'Fido'
            ],
            [
                'question' => 'Qual è il cognome da nubile di tua madre?',
                'answer' => 'Rossi'
            ],
            [
                'question' => 'In che città sei nato/a?',
                'answer' => 'Milano'
            ]
        ];
        
        foreach ($clients as $index => $client) {
            if (!$client->securityQuestion && isset($testQuestions[$index])) {
                SecurityQuestion::create([
                    'user_id' => $client->id,
                    'question' => $testQuestions[$index]['question'],
                    'answer_hash' => Hash::make(strtolower(trim($testQuestions[$index]['answer'])))
                ]);
                
                $this->command->info("Domanda di sicurezza aggiunta per {$client->full_name}");
                $this->command->info("Domanda: {$testQuestions[$index]['question']}");
                $this->command->info("Risposta: {$testQuestions[$index]['answer']}");
                $this->command->info("---");
            }
        }
        
        $this->command->info('Domande di sicurezza di test create!');
        $this->command->info('Le password per tutti gli utenti sono:');
        $this->command->info('- Admin: admin123');
        $this->command->info('- Dipendenti: employee123');
        $this->command->info('- Clienti: password123');
    }
}