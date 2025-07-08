<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Mostra i dati del profilo -
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Mostra il form per modificare i dati personali 
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Aggiorna i dati personali direttamente
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Prepara i dati per l'aggiornamento
        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        // Controlla se ci sono stati cambiamenti
        $hasChanges = false;
        foreach ($updateData as $key => $value) {
            if ($user->$key != $value) {
                $hasChanges = true;
                break;
            }
        }

        if (!$hasChanges) {
            return back()->with('info', 'Nessuna modifica rilevata.');
        }

        try {
            // Aggiorna direttamente i dati
            $user->update($updateData);

            // Redirect basato sul ruolo
            $redirectRoute = $this->getProfileRoute($user);

            return redirect()->route($redirectRoute)
                ->with('success', 'Dati personali aggiornati con successo.');

        } catch (\Exception $e) {
            \Log::error('Profile update failed:', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['general' => 'Errore durante l\'aggiornamento del profilo.'])->withInput();
        }
    }

    /**
     * Mostra il form per cambiare password 
     */
    public function showChangePassword()
    {
        return view('profile.change-password');
    }

    /**
     * Cambia la password direttamente 
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        // Verifica password attuale
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La password attuale non è corretta.']);
        }

        // Verifica che la nuova password sia diversa
        if (Hash::check($request->new_password, $user->password)) {
            return back()->withErrors(['new_password' => 'La nuova password deve essere diversa da quella attuale.']);
        }

        try {
            // Aggiorna la password direttamente
            $user->update(['password' => Hash::make($request->new_password)]);

            // Redirect basato sul ruolo
            $redirectRoute = $this->getProfileRoute($user);

            return redirect()->route($redirectRoute)
                ->with('success', 'Password cambiata con successo.');

        } catch (\Exception $e) {
            \Log::error('Password change failed:', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['general' => 'Errore durante il cambio password.']);
        }
    }

    /**
     * Cancella le modifiche in corso 
     */
    public function cancelChanges()
    {
        $user = Auth::user();
        $redirectRoute = $this->getProfileRoute($user);
        
        return redirect()->route($redirectRoute)
            ->with('info', 'Operazione annullata.');
    }

    /**
     * Metodi di compatibilità 
     */
    public function confirmChanges(Request $request)
    {
        return $this->update($request);
    }

    public function confirmPasswordChange(Request $request)
    {
        return $this->changePassword($request);
    }

    /**
     * Determina la rotta del profilo in base al ruolo
     */
    private function getProfileRoute($user): string
    {
        if ($user->isAdmin()) {
            return 'admin.profile.show';
        } elseif ($user->isEmployee()) {
            return 'employee.profile.show';
        } else {
            return 'client.profile.show';
        }
    }

    /**
     * Determina la dashboard in base al ruolo 
     */
    private function getDashboardRoute($user): string
    {
        if ($user->isAdmin()) {
            return 'dashboard.admin';
        } elseif ($user->isEmployee()) {
            return 'dashboard.employee';
        } else {
            return 'dashboard.cliente';
        }
    }
}