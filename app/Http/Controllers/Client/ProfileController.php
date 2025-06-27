<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Mostra i dati del profilo
     */
    public function show()
    {
        $user = Auth::user();
        return view('client.profile.show', compact('user'));
    }

    /**
     * Mostra il form per modificare i dati personali
     */
    public function edit()
    {
        $user = Auth::user();
        return view('client.profile.edit', compact('user'));
    }

    /**
     * Aggiorna i dati personali
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

        $changes = [];
        
        // Verifica cosa è cambiato
        if ($user->first_name !== $request->first_name) {
            $changes['first_name'] = $request->first_name;
        }
        if ($user->last_name !== $request->last_name) {
            $changes['last_name'] = $request->last_name;
        }
        if ($user->email !== $request->email) {
            $changes['email'] = $request->email;
        }
        if ($user->phone !== $request->phone) {
            $changes['phone'] = $request->phone;
        }
        if ($user->address !== $request->address) {
            $changes['address'] = $request->address;
        }

        if (empty($changes)) {
            return back()->withErrors(['general' => 'Nessuna modifica rilevata.']);
        }

        // Salva le modifiche in sessione per la conferma OTP
        session([
            'profile_changes' => $changes,
            'profile_change_confirmed' => false
        ]);

        // Se cambia l'email, richiedi OTP
        if (isset($changes['email'])) {
            $this->otpService->generateOtp($user, 'profile_change');
            
            return view('client.profile.confirm-otp', [
                'changes' => $changes,
                'development_otp' => app()->environment('local') ? $this->otpService->getLastOtpForDevelopment($user) : null
            ]);
        }

        // Altrimenti applica direttamente le modifiche
        $user->update($changes);

        return redirect()->route('client.profile.show')
            ->with('success', 'Dati personali aggiornati con successo.');
    }

    /**
     * Conferma le modifiche con OTP
     */
    public function confirmChanges(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $user = Auth::user();
        $changes = session('profile_changes');

        if (!$changes) {
            return redirect()->route('client.profile.edit')
                ->withErrors(['general' => 'Sessione scaduta. Riprovare.']);
        }

        // Verifica OTP
        if (!$this->otpService->verifyOtp($user, $request->otp, 'profile_change')) {
            return back()->withErrors(['otp' => 'Codice OTP non valido o scaduto.']);
        }

        // Applica le modifiche
        $user->update($changes);

        // Pulisci la sessione
        session()->forget(['profile_changes', 'profile_change_confirmed']);

        return redirect()->route('client.profile.show')
            ->with('success', 'Dati personali aggiornati con successo.');
    }

    /**
     * Mostra il form per cambiare password
     */
    public function showChangePassword()
    {
        return view('client.profile.change-password');
    }

    /**
     * Cambia la password
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

        // Genera OTP per conferma
        $this->otpService->generateOtp($user, 'password_change');
        
        session([
            'new_password' => Hash::make($request->new_password),
        ]);

        return view('client.profile.confirm-password-otp', [
            'development_otp' => app()->environment('local') ? $this->otpService->getLastOtpForDevelopment($user) : null
        ]);
    }

    /**
     * Conferma il cambio password con OTP
     */
    public function confirmPasswordChange(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $user = Auth::user();
        $newPassword = session('new_password');

        if (!$newPassword) {
            return redirect()->route('client.profile.change-password')
                ->withErrors(['general' => 'Sessione scaduta. Riprovare.']);
        }

        // Verifica OTP
        if (!$this->otpService->verifyOtp($user, $request->otp, 'password_change')) {
            return back()->withErrors(['otp' => 'Codice OTP non valido o scaduto.']);
        }

        // Aggiorna la password
        $user->update(['password' => $newPassword]);

        // Pulisci la sessione
        session()->forget('new_password');

        return redirect()->route('client.profile.show')
            ->with('success', 'Password cambiata con successo.');
    }

    /**
     * Cancella le modifiche in corso
     */
    public function cancelChanges()
    {
        session()->forget(['profile_changes', 'profile_change_confirmed', 'new_password']);
        
        return redirect()->route('client.profile.show')
            ->with('info', 'Modifiche annullate.');
    }
}