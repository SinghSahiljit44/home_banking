@extends('layouts.bootstrap')

@section('title', 'Bonifico Completato')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-success">
                <div class="card-header bg-success text-dark">
                    <div class="text-center">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h3>Bonifico Amministrativo Completato</h3>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Messaggio di successo -->
                    <div class="alert alert-success text-center mb-4">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Operazione eseguita con successo!</strong>
                        <br>
                        Il bonifico è stato elaborato immediatamente per conto del cliente.
                    </div>

                    <!-- Dettagli della transazione -->
                    <div class="card bg-dark border-secondary mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-receipt me-2"></i>Riepilogo Operazione</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Cliente:</strong> {{ $client->full_name }}</p>
                                    <p><strong>Email Cliente:</strong> {{ $client->email }}</p>
                                    <p><strong>Conto di Addebito:</strong> {{ $client->account->account_number }}</p>
                                    <p><strong>IBAN Mittente:</strong> <span class="font-monospace">{{ $client->account->iban }}</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Beneficiario:</strong> {{ $beneficiary_name ?: 'Non specificato' }}</p>
                                    <p><strong>IBAN Destinatario:</strong> <span class="font-monospace">{{ $recipient_iban }}</span></p>
                                    <p><strong>Importo:</strong> <span class="text-success h5">€{{ number_format($amount, 2, ',', '.') }}</span></p>
                                    <p><strong>Nuovo Saldo Cliente:</strong> <span class="text-info">€{{ number_format($new_balance, 2, ',', '.') }}</span></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <p><strong>Causale:</strong> {{ $description }}</p>
                                    <p><strong>Codice Riferimento:</strong> 
                                        <span class="font-monospace text-info" onclick="copyToClipboard('{{ $reference_code }}')" style="cursor: pointer;" title="Clicca per copiare">
                                            {{ $reference_code }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informazioni amministrative -->
                    <div class="card bg-warning bg-opacity-10 border-warning mb-4">
                        <div class="card-header">
                            <h6><i class="fas fa-user-shield me-2"></i>Informazioni Amministrative</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Operatore:</strong> {{ Auth::user()->full_name }}</p>
                                    <p><strong>Ruolo Operatore:</strong> {{ ucfirst(Auth::user()->role) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Data/Ora:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
                                    <p><strong>IP Operatore:</strong> {{ request()->ip() }}</p>
                                </div>
                            </div>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>Questa operazione è stata registrata nei log di sistema per motivi di sicurezza e audit.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Azioni -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-grid">
                                <a href="{{ route('admin.users.show', $client) }}" class="btn btn-primary">
                                    <i class="fas fa-user me-2"></i>Torna al Cliente
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid">
                                <a href="{{ route('admin.transactions.show', $transaction) }}" class="btn btn-info">
                                    <i class="fas fa-eye me-2"></i>Vedi Transazione
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid">
                                <button class="btn btn-secondary" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>Stampa Ricevuta
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Link rapidi -->
                    <div class="text-center mt-4">
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.transactions.create-transfer-form', $client) }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-redo me-1"></i>Nuovo Bonifico per questo Cliente
                            </a>
                            <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-list me-1"></i>Tutte le Transazioni
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-users me-1"></i>Gestione Utenti
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast per copia codice -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="copyToast" class="toast" role="alert">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto">Copiato!</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            Codice riferimento copiato negli appunti.
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        const toast = new bootstrap.Toast(document.getElementById('copyToast'));
        toast.show();
    });
}

// Auto-scroll alla parte superiore della pagina
document.addEventListener('DOMContentLoaded', function() {
    window.scrollTo(0, 0);
    
    // Aggiungi animazione di entrata
    const card = document.querySelector('.card');
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    
    setTimeout(function() {
        card.style.transition = 'all 0.5s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 100);
});

// Evidenzia il codice riferimento al hover
document.addEventListener('DOMContentLoaded', function() {
    const referenceCode = document.querySelector('.font-monospace.text-info');
    if (referenceCode) {
        referenceCode.addEventListener('mouseenter', function() {
            this.classList.add('bg-info', 'text-dark');
        });
        referenceCode.addEventListener('mouseleave', function() {
            this.classList.remove('bg-info', 'text-dark');
        });
    }
});
</script>

<style>
@media print {
    .btn, .btn-group, .toast-container { 
        display: none !important; 
    }
    .card { 
        border: 2px solid #000 !important; 
        box-shadow: none !important;
    }
    body { 
        color: #000 !important; 
        background: white !important; 
    }
    .bg-dark {
        background: #f8f9fa !important;
        color: #000 !important;
    }
    .text-info, .text-success, .text-warning {
        color: #000 !important;
    }
}

.font-monospace {
    transition: all 0.2s ease;
    padding: 2px 4px;
    border-radius: 3px;
}

.card {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3);
}

.alert {
    border-left: 4px solid;
}

.alert-success {
    border-left-color: #28a745;
}

.alert-info {
    border-left-color: #17a2b8;
}
</style>
@endsection