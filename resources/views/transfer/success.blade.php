@extends('layouts.bootstrap')

@section('title', 'Bonifico Completato')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-success">
                <div class="card-header bg-success text-center">
                    <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i>Bonifico Completato</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-success text-center">
                        <h5><i class="fas fa-thumbs-up me-2"></i>{{ $message }}</h5>
                        <p class="mb-0">La tua operazione è stata elaborata con successo.</p>
                    </div>

                    <!-- Dettagli transazione -->
                    <div class="card bg-dark border-secondary mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Dettagli Transazione</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Codice Riferimento:</strong></p>
                                    <p class="text-info font-monospace mb-3">{{ $reference_code }}</p>
                                    
                                    <p class="mb-2"><strong>Data e Ora:</strong></p>
                                    <p class="mb-3">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</p>
                                    
                                    <p class="mb-2"><strong>Importo:</strong></p>
                                    <p class="text-warning h5 mb-3">€{{ number_format($transaction->amount, 2, ',', '.') }}</p>
                                </div>
                                <div class="col-md-6">
                                    @if($transaction->toAccount)
                                        <p class="mb-2"><strong>Beneficiario:</strong></p>
                                        <p class="mb-3">{{ $transaction->toAccount->user->full_name }}</p>
                                        
                                        <p class="mb-2"><strong>IBAN Destinazione:</strong></p>
                                        <p class="font-monospace mb-3">{{ $transaction->toAccount->iban }}</p>
                                    @else
                                        <p class="mb-2"><strong>Tipo:</strong></p>
                                        <p class="mb-3">Bonifico Esterno</p>
                                    @endif
                                    
                                    <p class="mb-2"><strong>Causale:</strong></p>
                                    <p class="mb-3">{{ $transaction->description }}</p>
                                    
                                    <p class="mb-2"><strong>Stato:</strong></p>
                                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }} mb-3">
                                        {{ $transaction->status === 'completed' ? 'Completato' : 'In Elaborazione' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informazioni aggiuntive -->
                    @if($transaction->status === 'pending')
                        <div class="alert alert-warning">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Bonifico in elaborazione:</strong> L'operazione verso banche esterne richiede 1-2 giorni lavorativi per essere completata.
                        </div>
                    @endif

                    <!-- Azioni -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-outline-light" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>Stampa Ricevuta
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-outline-info" onclick="downloadReceipt()">
                                    <i class="fas fa-download me-2"></i>Scarica PDF
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6 mb-2">
                            <div class="d-grid">
                                <a href="{{ route('client.transfer.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Nuovo Bonifico
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="d-grid">
                                <a href="{{ route('dashboard.cliente') }}" class="btn btn-primary">
                                    <i class="fas fa-home me-2"></i>Torna alla Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stile per stampa -->
<style media="print">
    .btn, .navbar, .card-header { display: none !important; }
    .card { border: 1px solid #000 !important; }
    body { color: #000 !important; background: white !important; }
    .text-info, .text-warning, .text-success { color: #000 !important; }
</style>

<script>
function downloadReceipt() {
    // In un'implementazione reale, questo genererebbe un PDF
    alert('Funzionalità in sviluppo: Il download del PDF sarà disponibile prossimamente.');
}

// Auto-focus per accessibilità
document.addEventListener('DOMContentLoaded', function() {
    // Evidenzia il codice di riferimento per facilità di copia
    const referenceCode = document.querySelector('.font-monospace');
    if (referenceCode) {
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
        
        referenceCode.style.cursor = 'pointer';
        referenceCode.title = 'Clicca per copiare';
    }
});
</script>
@endsection