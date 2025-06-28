<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SecurityQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SecurityQuestionController extends Controller
{
    /**
     * Lista delle domande predefinite
     */
    private $availableQuestions = [
        'Qual è il nome del tuo primo animale domestico?',
        'Qual è il cognome da nubile di tua madre?',
        'In che città sei nato/a?',
        'Qual è il nome della tua scuola elementare?',
        'Qual è il tuo piatto preferito?',
        'Qual è il nome del tuo migliore amico d\'infanzia?',
        'Qual è il modello della tua prima auto?',
        'In che anno ti sei diplomato/a?',
        'Qual è il nome della strada dove sei cresciuto/a?',
        'Qual è il tuo colore preferito?',
        'Qual è il nome del tuo primo capo?',
        'Qual è la tua squadra del cuore?',
    ];

    /**
     * Mostra la pagina delle domande di sicurezza
     */
    public function index()
    {
        $user = Auth::user();
        $securityQuestion = $user->securityQuestion;
        $availableQuestions = $this->availableQuestions;

        return view('client.security.questions', compact('securityQuestion', 'availableQuestions'));
    }

    /**
     * Salva o aggiorna la domanda di sicurezza
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'answer' => 'required|string|min:3|max:100',
            'current_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        // Verifica la password attuale
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La password inserita non è corretta.'])->withInput();
        }

        // Verifica che la domanda sia nell'elenco predefinito
        if (!in_array($request->question, $this->availableQuestions)) {
            return back()->withErrors(['question' => 'Seleziona una domanda dall\'elenco.'])->withInput();
        }

        try {
            // Elimina la domanda esistente se presente
            if ($user->securityQuestion) {
                $user->securityQuestion->delete();
            }

            // Crea la nuova domanda di sicurezza
            SecurityQuestion::create([
                'user_id' => $user->id,
                'question' => $request->question,
                'answer_hash' => Hash::make(strtolower(trim($request->answer))),
            ]);

            return redirect()->route('client.security.questions')
                ->with('success', 'Domanda di sicurezza configurata con successo.');

        } catch (\Exception $e) {
            \Log::error('Security question creation failed:', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['general' => 'Errore durante il salvataggio della domanda di sicurezza.'])->withInput();
        }
    }

    /**
     * Mostra il form per verificare la domanda di sicurezza
     */
    public function verify()
    {
        $user = Auth::user();
        
        if (!$user->securityQuestion) {
            return redirect()->route('client.security.questions')
                ->withErrors(['general' => 'Non hai ancora configurato una domanda di sicurezza.']);
        }

        return view('client.security.verify', [
            'question' => $user->securityQuestion->question
        ]);
    }

    /**
     * Verifica la risposta alla domanda di sicurezza
     */
    public function checkAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        
        if (!$user->securityQuestion) {
            return redirect()->route('client.security.questions')
                ->withErrors(['general' => 'Domanda di sicurezza non trovata.']);
        }

        $isCorrect = $user->securityQuestion->checkAnswer($request->answer);

        if ($isCorrect) {
            return back()->with('success', 'Risposta corretta! La tua identità è stata verificata.');
        } else {
            return back()->withErrors(['answer' => 'Risposta non corretta. Riprova.']);
        }
    }

    /**
     * Elimina la domanda di sicurezza
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        // Verifica la password attuale
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La password inserita non è corretta.']);
        }

        if ($user->securityQuestion) {
            $user->securityQuestion->delete();
            return redirect()->route('client.security.questions')
                ->with('success', 'Domanda di sicurezza eliminata con successo.');
        }

        return back()->withErrors(['general' => 'Nessuna domanda di sicurezza da eliminare.']);
    }
}