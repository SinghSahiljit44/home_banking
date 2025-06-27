<?php

namespace App\Services;

use App\Models\User;
use App\Models\OtpToken;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class OtpService
{
    /**
     * Genera un nuovo OTP per l'utente
     */
    public function generateOtp(User $user, string $type): string
    {
        // Invalida tutti i token precedenti dello stesso tipo
        OtpToken::where('user_id', $user->id)
                ->where('type', $type)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

        // Genera un nuovo token di 6 cifre
        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Crea il record OTP
        OtpToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(5), // Scade dopo 5 minuti
        ]);

        // Simula invio SMS/Email (in produzione usare servizi reali)
        $this->sendOtp($user, $token, $type);

        return $token;
    }

    /**
     * Verifica un OTP
     */
    public function verifyOtp(User $user, string $token, string $type): bool
    {
        $otpRecord = OtpToken::where('user_id', $user->id)
                            ->where('token', $token)
                            ->where('type', $type)
                            ->whereNull('used_at')
                            ->first();

        if (!$otpRecord) {
            return false;
        }

        if ($otpRecord->isExpired()) {
            return false;
        }

        // Marca il token come usato
        $otpRecord->update(['used_at' => now()]);

        return true;
    }

    /**
     * Simula l'invio dell'OTP (in sviluppo)
     */
    private function sendOtp(User $user, string $token, string $type): void
    {
        // In un ambiente di produzione, qui integreresti:
        // - Servizio SMS (Twilio, AWS SNS, etc.)
        // - Servizio Email
        
        // Per ora logghiamo il token per debug
        \Log::info("OTP generato per {$user->email}: {$token} (Tipo: {$type})");
        
        // In sviluppo, potremmo salvare l'OTP in sessione per test
        session(['last_otp_' . $user->id => $token]);
    }

    /**
     * Ottiene l'ultimo OTP generato (solo per sviluppo)
     */
    public function getLastOtpForDevelopment(User $user): ?string
    {
        return session('last_otp_' . $user->id);
    }
}