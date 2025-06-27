<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notifica Transazione</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
        .content { background: #f8f9fa; padding: 30px; }
        .transaction-box { background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .amount { font-size: 24px; font-weight: bold; text-align: center; padding: 15px; }
        .amount.incoming { color: #28a745; }
        .amount.outgoing { color: #dc3545; }
        .footer { background: #343a40; color: white; padding: 15px; text-align: center; font-size: 12px; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏦 Home Banking</h1>
            <h2>Notifica Transazione</h2>
        </div>

        <div class="content">
            <p>Gentile <strong>{{ $user->full_name }}</strong>,</p>
            
            <p>Ti informiamo che è stata completata una transazione sul tuo conto corrente:</p>

            <div class="transaction-box">
                <div class="amount {{ $type }}">
                    @if($type === 'outgoing')
                        -€{{ number_format($transaction->amount, 2, ',', '.') }}
                    @else
                        +€{{ number_format($transaction->amount, 2, ',', '.') }}
                    @endif
                </div>

                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Data:</strong></td>
                        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Tipo:</strong></td>
                        <td style="padding: 8px; border-bottom: 1px solid #eee;">
                            @switch($transaction->type)
                                @case('transfer_in') Bonifico Ricevuto @break
                                @case('transfer_out') Bonifico Inviato @break
                                @case('deposit') Deposito @break
                                @case('withdrawal') Prelievo @break
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Descrizione:</strong></td>
                        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $transaction->description }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Riferimento:</strong></td>
                        <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $transaction->reference_code }}</td>
                    </tr>
                    @if($type === 'outgoing' && $transaction->toAccount)
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Beneficiario:</strong></td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $transaction->toAccount->user->full_name }}</td>
                        </tr>
                    @elseif($type === 'incoming' && $transaction->fromAccount)
                        <tr>
                            <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Ordinante:</strong></td>
                            <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $transaction->fromAccount->user->full_name }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <p style="text-align: center;">
                <a href="{{ url('/client/account') }}" class="btn">Visualizza Estratto Conto</a>
            </p>

            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 15px; margin: 20px 0;">
                <p style="margin: 0;"><strong>⚠️ Importante:</strong></p>
                <p style="margin: 5px 0 0 0;">Se non hai autorizzato questa operazione, contatta immediatamente il nostro servizio clienti.</p>
            </div>
        </div>

        <div class="footer">
            <p>Questa è una email automatica, non rispondere a questo messaggio.</p>
            <p>© {{ date('Y') }} Home Banking - Tutti i diritti riservati</p>
        </div>
    </div>
</body>
</html>