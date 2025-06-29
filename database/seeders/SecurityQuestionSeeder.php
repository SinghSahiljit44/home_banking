<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SecurityQuestion;
use Illuminate\Support\Facades\Hash;

class SecurityQuestionSeeder extends Seeder
{
    private $questionsAndAnswers = [
        [
            'question' => 'Qual è il nome del tuo primo animale domestico?',
            'answers' => ['Fido', 'Luna', 'Max', 'Stella', 'Rex', 'Micia', 'Leo', 'Bella', 'Charlie', 'Lucy']
        ],
        [
            'question' => 'Qual è il cognome da nubile di tua madre?',
            'answers' => ['Rossi', 'Bianchi', 'Ferrari', 'Romano', 'Ricci', 'Marino', 'Greco', 'Bruno', 'Gallo', 'Conti']
        ],
        [
            'question' => 'In che città sei nato/a?',
            'answers' => ['Milano', 'Roma', 'Napoli', 'Torino', 'Firenze', 'Bologna', 'Genova', 'Palermo', 'Bari', 'Catania']
        ],
        [
            'question' => 'Qual è il nome della tua scuola elementare?',
            'answers' => ['Manzoni', 'Garibaldi', 'Marconi', 'Volta', 'Fermi', 'Dante', 'Leopardi', 'Carducci', 'Petrarca', 'Pascoli']
        ],
        [
            'question' => 'Qual è il tuo piatto preferito?',
            'answers' => ['Pizza', 'Pasta', 'Risotto', 'Lasagne', 'Tiramisù', 'Carbonara', 'Amatriciana', 'Parmigiana', 'Ossobuco', 'Minestrone']
        ],
        [
            'question' => 'Qual è il nome del tuo migliore amico d\'infanzia?',
            'answers' => ['Marco', 'Luca', 'Anna', 'Sara', 'Paolo', 'Giulia', 'Andrea', 'Francesca', 'Matteo', 'Chiara']
        ],
        [
            'question' => 'Qual è il modello della tua prima auto?',
            'answers' => ['Fiat Punto', 'Volkswagen Golf', 'Opel Corsa', 'Ford Fiesta', 'Peugeot 206', 'Renault Clio', 'Lancia Ypsilon', 'Alfa Romeo 147', 'BMW Serie 1', 'Audi A3']
        ],
        [
            'question' => 'In che anno ti sei diplomato/a?',
            'answers' => ['1995', '1998', '2000', '2002', '2005', '2008', '2010', '2012', '2015', '2018']
        ],
        [
            'question' => 'Qual è il nome della strada dove sei cresciuto/a?',
            'answers' => ['Via Roma', 'Via Manzoni', 'Corso Italia', 'Via Garibaldi', 'Piazza Duomo', 'Via Dante', 'Corso Venezia', 'Via Milano', 'Largo Argentina', 'Via Nazionale']
        ],
        [
            'question' => 'Qual è il tuo colore preferito?',
            'answers' => ['Blu', 'Rosso', 'Verde', 'Giallo', 'Nero', 'Bianco', 'Viola', 'Arancione', 'Rosa', 'Grigio']
        ],
    ];

    public function run(): void
    {
        $clients = User::where('role', 'client')->get();
        
        // Il 70% dei clienti avrà una domanda di sicurezza
        $clientsWithQuestions = $clients->random(ceil($clients->count() * 0.7));
        
        foreach ($clientsWithQuestions as $client) {
            // Verifica che non abbia già una domanda
            if ($client->securityQuestion) {
                continue;
            }
            
            $questionData = $this->questionsAndAnswers[array_rand($this->questionsAndAnswers)];
            $question = $questionData['question'];
            $answer = $questionData['answers'][array_rand($questionData['answers'])];
            
            SecurityQuestion::create([
                'user_id' => $client->id,
                'question' => $question,
                'answer_hash' => Hash::make(strtolower(trim($answer))),
            ]);
            
            $this->command->info("Domanda di sicurezza creata per {$client->full_name}: {$question} -> {$answer}");
        }
        
        $this->command->info('Domande di sicurezza create!');
    }
}
