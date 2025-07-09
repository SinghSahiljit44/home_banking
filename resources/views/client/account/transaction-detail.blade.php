@extends('layouts.bootstrap')

@section('title', 'Dettagli Transazione')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-receipt me-2"></i>Dettagli Transazione</h4>
                        <a href="{{ route('client.account.show') }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Torna all'Estratto Conto
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Stato Transazione -->
                    <div class="text-center mb-4">
                        @switch($transaction->status)
                            @case('completed')
                                <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                                <h5 class="text-success">Transazione Completata</h5>
                                @break
                            @case('pending')
                                <i class="fas fa-clock fa-3x text-warning mb-2"></i>
                                <h5 class="text-warning">Transazione in Elaborazione</h5>
                                @break
                            @case('failed')
                                <i class="fas fa-times-circle fa-3x text-danger mb-2"></i>
                                <h5 class="text-danger">Transazione Fallita</h5>
                                @break
                        @endswitch
                    </div>

                    <!-- Dettagli Principali -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-dark border-secondary mb-3">
                                <div class="card-header">
                                    <h6><i class="fas fa-info-circle me-2"></i>Informazioni Generali</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Codice Riferimento:</strong></p>
                                    <p class="text-info font-monospace mb-3">{{ $transaction->reference_code }}</p>
                                    
                                    <p><strong>Data e Ora:</strong></p>
                                    <p class="mb-3">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</p>
                                    
                                    <p><strong>Tipo Operazione:</strong></p>
                                    <p class="mb-0">
                                        @switch($transaction->type)
                                            @case('transfer_in')
                                                <span class="badge bg-success">Bonifico Ricevuto</span>
                                                @break
                                            @case('transfer_out')
                                                <span class="badge bg-primary">Bonifico Inviato</span>
                                                @break
                                            @case('deposit')
                                                <span class="badge bg-success">Deposito</span>
                                                @break
                                            @case('withdrawal')
                                                <span class="badge bg-warning">Prelievo</span>
                                                @break
                                        @endswitch
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-dark border-secondary mb-3">
                                <div class="card-header">
                                    <h6><i class="fas fa-euro-sign me-2"></i>Importo</h6>
                                </div>
                                <div class="card-body text-center">
                                    @if($transaction->from_account_id === $account->id)
                                        <h2 class="text-danger">
                                            <i class="fas fa-arrow-down me-2"></i>
                                            -€{{ number_format($transaction->amount, 2, ',', '.') }}
                                        </h2>
                                        <p class="text-muted">Importo addebitato</p>
                                    @else
                                        <h2 class="text-success">
                                            <i class="fas fa-arrow-up me-2"></i>
                                            +€{{ number_format($transaction->amount, 2, ',', '.') }}
                                        </h2>
                                        <p class="text-muted">Importo accreditato</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dettagli Conto -->
                    <div class="card bg-dark border-secondary mb-3">
                        <div class="card-header">
                            <h6><i class="fas fa-exchange-alt me-2"></i>Dettagli Operazione</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    @if($transaction->fromAccount)
                                        <p><strong>Conto di Addebito:</strong></p>
                                        <p class="mb-2">{{ $transaction->fromAccount->user->full_name }}</p>
                                        <p class="font-monospace small text-muted mb-3">{{ $transaction->fromAccount->iban }}</p>
                                    @else
                                        <p><strong>Origine:</strong></p>
                                        <p class="mb-3">Sistema Bancario</p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    @if($transaction->toAccount)
                                        <p><strong>Conto di Accredito:</strong></p>
                                        <p class="mb-2">{{ $transaction->toAccount->user->full_name }}</p>
                                        <p class="font-monospace small text-muted mb-3">{{ $transaction->toAccount->iban }}</p>
                                    @else
                                        <p><strong>Destinazione:</strong></p>
                                        <p class="mb-3">Conto Esterno</p>
                                    @endif
                                </div>
                            </div>
                            
                            @if($transaction->description)
                                <div class="row">
                                    <div class="col-12">
                                        <p><strong>Causale:</strong></p>
                                        <p class="mb-0">{{ $transaction->description }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Stato e Informazioni Aggiuntive -->
                    @if($transaction->status === 'pending')
                        <div class="alert alert-warning">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Operazione in elaborazione:</strong> La transazione sarà completata entro 1-2 giorni lavorativi.
                        </div>
                    @elseif($transaction->status === 'failed')
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Operazione fallita:</strong> La transazione non è stata completata. Contatta l'assistenza per maggiori informazioni.
                        </div>
                    @endif

                    <!-- Azioni -->
                    <div class="row justify-content-center">
                        <div class="col-md-6 mb-2">
                            <div class="d-grid">
                                <button class="btn btn-outline-light" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>Stampa Dettagli
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function downloadPdf() {
    alert('Funzionalità in sviluppo: Il download del PDF sarà disponibile prossimamente.');
}

// Copia codice riferimento al click
document.addEventListener('DOMContentLoaded', function() {
    const referenceCode = document.querySelector('.font-monospace');
    if (referenceCode) {
        referenceCode.style.cursor = 'pointer';
        referenceCode.title = 'Clicca per copiare il codice';
        referenceCode.addEventListener('click', function() {
            navigator.clipboard.writeText(this.textContent).then(function() {
                const originalText = referenceCode.textContent;
                referenceCode.textContent = 'Copiato!';
                referenceCode.classList.add('text-success');
                
                setTimeout(function() {
                    referenceCode.textContent = originalText;
                    referenceCode.classList.remove('text-success');
                    referenceCode.classList.add('text-info');
                }, 2000);
            });
        });
    }
});
</script>

<style media="print">
    .btn, .navbar, .card-header { display: none !important; }
    .card { border: 1px solid #000 !important; }
    body { color: #000 !important; background: white !important; }
    .text-info, .text-warning, .text-success, .text-danger { color: #000 !important; }
</style>
@endsection