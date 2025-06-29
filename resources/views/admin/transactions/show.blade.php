@extends('layouts.bootstrap')

@section('title', 'Dettagli Transazione')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-receipt me-2"></i>Dettagli Transazione ID: {{ $transaction->id }}</h4>
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Torna alla Lista
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Stato e Azioni -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5>
                                Stato: 
                                @switch($transaction->status)
                                    @case('completed')
                                        <span class="badge bg-success fs-6">Completata</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning fs-6">In Sospeso</span>
                                        @break
                                    @case('failed')
                                        <span class="badge bg-danger fs-6">Fallita</span>
                                        @break
                                @endswitch
                            </h5>
                        </div>
                        <div class="col-md-4 text-end">
                            @if($transaction->status === 'pending')
                                <div class="btn-group" role="group">
                                    <form method="POST" action="{{ route('admin.transactions.approve', $transaction) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-success btn-sm" 
                                                onclick="return confirm('Confermi l\'approvazione di questa transazione?')">
                                            <i class="fas fa-check me-1"></i>Approva
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="{{ route('admin.transactions.reject', $transaction) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Confermi il rifiuto di questa transazione?')">
                                            <i class="fas fa-times me-1"></i>Rifiuta
                                        </button>
                                    </form>
                                </div>
                            @endif

                            @if($transaction->status === 'completed' && !str_contains($transaction->description, '[STORNATA]'))
                                <form method="POST" action="{{ route('admin.transactions.reverse', $transaction) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-warning btn-sm" 
                                            onclick="return confirm('ATTENZIONE: Questa operazione creerà una transazione di storno. Sei sicuro?')">
                                        <i class="fas fa-undo me-1"></i>Storna
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Informazioni Principali -->
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
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($transaction->type) }}</span>
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
                                    <h2 class="text-warning">
                                        €{{ number_format($transaction->amount, 2, ',', '.') }}
                                    </h2>
                                    <p class="text-muted">Importo della transazione</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dettagli Conti -->
                    <div class="card bg-dark border-secondary mb-3">
                        <div class="card-header">
                            <h6><i class="fas fa-exchange-alt me-2"></i>Dettagli Operazione</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-danger">Conto di Addebito:</h6>
                                    @if($transaction->fromAccount)
                                        <p class="mb-1"><strong>Titolare:</strong> {{ $transaction->fromAccount->user->full_name }}</p>
                                        <p class="mb-1"><strong>Numero Conto:</strong> {{ $transaction->fromAccount->account_number }}</p>
                                        <p class="mb-3"><strong>IBAN:</strong> <span class="font-monospace">{{ $transaction->fromAccount->iban }}</span></p>
                                        <p class="mb-1"><strong>Email:</strong> {{ $transaction->fromAccount->user->email }}</p>
                                        <p class="mb-3"><strong>Ruolo:</strong> 
                                            <span class="badge bg-{{ $transaction->fromAccount->user->role === 'admin' ? 'danger' : ($transaction->fromAccount->user->role === 'employee' ? 'warning' : 'success') }}">
                                                {{ ucfirst($transaction->fromAccount->user->role) }}
                                            </span>
                                        </p>
                                    @else
                                        <p class="text-muted">Sistema Bancario / Deposito Esterno</p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-success">Conto di Accredito:</h6>
                                    @if($transaction->toAccount)
                                        <p class="mb-1"><strong>Titolare:</strong> {{ $transaction->toAccount->user->full_name }}</p>
                                        <p class="mb-1"><strong>Numero Conto:</strong> {{ $transaction->toAccount->account_number }}</p>
                                        <p class="mb-3"><strong>IBAN:</strong> <span class="font-monospace">{{ $transaction->toAccount->iban }}</span></p>
                                        <p class="mb-1"><strong>Email:</strong> {{ $transaction->toAccount->user->email }}</p>
                                        <p class="mb-3"><strong>Ruolo:</strong> 
                                            <span class="badge bg-{{ $transaction->toAccount->user->role === 'admin' ? 'danger' : ($transaction->toAccount->user->role === 'employee' ? 'warning' : 'success') }}">
                                                {{ ucfirst($transaction->toAccount->user->role) }}
                                            </span>
                                        </p>
                                    @else
                                        <p class="text-muted">Conto Esterno / Bonifico Esterno</p>
                                    @endif
                                </div>
                            </div>
                            
                            @if($transaction->description)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6>Causale/Descrizione:</h6>
                                        <p class="mb-0 p-2 bg-secondary bg-opacity-25 rounded">{{ $transaction->description }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Informazioni Aggiuntive -->
                    @if($transaction->status === 'pending')
                        <div class="alert alert-warning">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Operazione in elaborazione:</strong> Questa transazione è in attesa di approvazione o elaborazione automatica.
                        </div>
                    @elseif($transaction->status === 'failed')
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Operazione fallita:</strong> La transazione non è stata completata. Verificare i log per maggiori dettagli.
                        </div>
                    @elseif(str_contains($transaction->description, '[STORNATA]'))
                        <div class="alert alert-info">
                            <i class="fas fa-undo me-2"></i>
                            <strong>Transazione stornata:</strong> Questa transazione è stata stornata da un amministratore.
                        </div>
                    @endif

                    <!-- Azioni Amministrative -->
                    <div class="card bg-dark border-secondary">
                        <div class="card-header">
                            <h6><i class="fas fa-tools me-2"></i>Azioni Amministrative</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-grid">
                                        <button class="btn btn-outline-light" onclick="window.print()">
                                            <i class="fas fa-print me-2"></i>Stampa Dettagli
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-grid">
                                        @if($transaction->fromAccount)
                                            <a href="{{ route('admin.users.show', $transaction->fromAccount->user) }}" class="btn btn-outline-info">
                                                <i class="fas fa-user me-2"></i>Vedi Mittente
                                            </a>
                                        @else
                                            <button class="btn btn-outline-secondary" disabled>
                                                <i class="fas fa-user-slash me-2"></i>Mittente Esterno
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-grid">
                                        @if($transaction->toAccount)
                                            <a href="{{ route('admin.users.show', $transaction->toAccount->user) }}" class="btn btn-outline-success">
                                                <i class="fas fa-user me-2"></i>Vedi Destinatario
                                            </a>
                                        @else
                                            <button class="btn btn-outline-secondary" disabled>
                                                <i class="fas fa-user-slash me-2"></i>Destinatario Esterno
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif

<style media="print">
    .btn, .navbar, .card-header { display: none !important; }
    .card { border: 1px solid #000 !important; }
    body { color: #000 !important; background: white !important; }
    .text-info, .text-warning, .text-success, .text-danger { color: #000 !important; }
</style>

<script>
// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});

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
@endsection