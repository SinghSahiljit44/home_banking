<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransferController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Mostra il form per il bonifico
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->account || !$user->account->is_active) {
            return redirect()->route('dashboard.cliente')
                ->withErrors(['account' => 'Account non disponibile per effettuare bonifici.']);
        }

        return view('client.transfer.create', [
            'account' => $user->account
        ]);
    }

    /**
     * Elabora e completa direttamente il bonifico
     */
    public function store(Request $request)
    {
        // Rimuovi spazi dall'IBAN prima della validazione
        $cleanIban = strtoupper(str_replace(' ', '', $request->recipient_iban));
        $request->merge(['recipient_iban' => $cleanIban]);

        // Debug: log dei dati ricevuti
        \Log::info('Transfer request data:', [
            'recipient_iban' => $request->recipient_iban,
            'iban_length' => strlen($request->recipient_iban),
            'amount' => $request->amount,
            'description' => $request->description
        ]);

        $validator = Validator::make($request->all(), [
            'recipient_iban' => 'required|string|min:15|max:34',
            'amount' => 'required|numeric|min:0.01|max:50000',
            'description' => 'required|string|max:255',
            'beneficiary_name' => 'nullable|string|max:100',
        ]);

        // Validazione personalizzata
        $validator->after(function ($validator) use ($request) {
            $iban = $request->recipient_iban;
            
            // Verifica formato base IBAN (2 lettere + 2 numeri + caratteri alfanumerici)
            if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/', $iban)) {
                $validator->errors()->add('recipient_iban', 'Formato IBAN non valido. Deve iniziare con 2 lettere e 2 numeri.');
            }

            // Verifica lunghezza specifica per IBAN italiani
            if (substr($iban, 0, 2) === 'IT' && strlen($iban) !== 27) {
                $validator->errors()->add('recipient_iban', 'L\'IBAN italiano deve essere di esattamente 27 caratteri (senza spazi).');
            }
        });

        if ($validator->fails()) {
            \Log::error('Transfer validation failed:', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $account = $user->account;

        // Verifica saldo
        if (!$account->hasSufficientBalance($request->amount)) {
            return back()->withErrors(['amount' => 'Saldo insufficiente.'])->withInput();
        }

        // Verifica che non stia inviando a se stesso
        if ($request->recipient_iban === $account->iban) {
            return back()->withErrors(['recipient_iban' => 'Non puoi inviare denaro al tuo stesso conto.'])->withInput();
        }

        try {
            // Esegui direttamente il bonifico
            $result = $this->transactionService->processBonifico(
                $account,
                $request->recipient_iban,
                $request->amount,
                $request->description,
                $request->beneficiary_name
            );

            if ($result['success']) {
                return view('client.transfer.success', [
                    'transaction' => $result['transaction'],
                    'reference_code' => $result['reference_code'],
                    'message' => $result['message']
                ]);
            } else {
                return redirect()->route('client.transfer.create')
                    ->withErrors(['general' => $result['message']]);
            }

        } catch (\Exception $e) {
            \Log::error('Transfer processing failed:', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'amount' => $request->amount
            ]);

            return back()->withErrors(['general' => 'Errore durante l\'elaborazione del bonifico. Riprova più tardi.'])->withInput();
        }
    }

    /**
     * Cancella il bonifico in corso (ora redirecta semplicemente)
     */
    public function cancel()
    {
        return redirect()->route('client.transfer.create')
            ->with('info', 'Operazione annullata.');
    }
}