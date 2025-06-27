<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attività Sospetta Rilevata</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { background: #f8f9fa; padding: 30px; }
        .alert-box { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .footer { background: #343a40; color: white; padding: 15px; text-align: center; font-size: 12px; }
        .btn { display: inline-block; padding: 12px 24px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 ALLERTA SICUREZZA</h1>
            <h2>Attività Sospetta Rilevata</h2>
        </div>

        <div class="content">
            <p>Gentile <strong>{{ $user->full_name }}</strong>,</p>
            
            <div class="alert-box">
                <h3 style="color: #721c24; margin-top: 0;">⚠️ ATTENZIONE</h3>
                <p>Abbiamo rilevato un'attività sospetta sul tuo account:</p>
                <p><strong>{{ $activity }}</strong></p>
                
                @if($details)
                    <hr>
                    <h4>Dettagli:</h4>
                    <ul>
                        @foreach($details as $key => $value)
                            <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</li>
                        @endforeach
                    </ul>
                @endif
                
                <p><strong>Data e Ora:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
            </div>

            <h3>🔒 Cosa fare ora:</h3>
            <ol>
                <li><strong>Verifica</strong> se sei stato tu a compiere questa azione</li>
                <li><strong>Cambia immediatamente</strong> la tua password se sospetti accessi non autorizzati</li>
                <li><strong>Contatta</strong> il nostro servizio clienti se non riconosci l'attività</li>
                <li><strong>Controlla</strong> i tuoi movimenti recenti nell'estratto conto</li>
            </ol>

            <p style="text-align: center;">
                <a href="{{ url('/client/profile/change-password') }}" class="btn">Cambia Password</a>
            </p>

            <div style="background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; padding: 15px; margin: 20px 0;">
                <p style="margin: 0;"><strong>💡 Suggerimento per la sicurezza:</strong></p>
                <p style="margin: 5px 0 0 0;">Non condividere mai le tue credenziali e accedi sempre da dispositivi fidati.</p>
            </div>
        </div>

        <div class="footer">
            <p><strong>Servizio Clienti:</strong> 800-123-456 (attivo 24/7)</p>
            <p>© {{ date('Y') }} Home Banking - Tutti i diritti riservati</p>
        </div>
    </div>
</body>
</html>