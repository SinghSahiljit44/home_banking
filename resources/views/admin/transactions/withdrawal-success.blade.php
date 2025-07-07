@extends('layouts.bootstrap')

@section('title', 'Prelievo Completato')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-success">
                <div class="card-header bg-success text-white">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <div>
                            <h4 class="mb-0">Prelievo Completato con Successo</h4>
                            <small>Operazione eseguita da {{ Auth::user()->full_name }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Dettagli Prelievo -->
                    <div class="card bg-dark border-secondary mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-receipt me-2"></i>Dettagli Prelievo</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Cliente:</strong> {{ $client->full_name }}</p>
                                    <p><strong>Conto:</strong> {{ $client->account->account_number }}</p>
                                    <p><strong>IBAN:</strong> <span class="font-monospace">{{ $client->account->iban }}</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Data/Ora:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
                                    <p><strong>Riferimento:</strong> <span class="font-monospace">{{ $transaction->reference_code }}</span></p>
                                    <p><strong>Operatore:</strong> {{ Auth::user()->full_name }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <p><strong>Descrizione:</strong> {{ $description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Importi -->
                    <div class="card bg-dark border-secondary mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-calculator me-2"></i>Riepilogo Importi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="border-end border-secondary">
                                        <h4 class="text-info">€{{ number_format($previous_balance, 2, ',', '.') }}</h4>
                                        <p class="mb-0 text-muted">Saldo Precedente</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border-end border-secondary">
                                        <h4 class="text-danger">-€{{ number_format($amount, 2, ',', '.') }}</h4>
                                        <p class="mb-0 text-muted">Importo Prelevato</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h4 class="text-success">€{{ number_format($new_balance, 2, ',', '.') }}</h4>
                                    <p class="mb-0 text-muted">Nuovo Saldo</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informazioni di Sicurezza -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Nota di Sicurezza:</strong> Questa operazione è stata registrata nei log di sistema e sarà tracciabile negli audit.
                    </div>

                    <!-- Azioni -->
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <div>
                            <a href="{{ route('admin.users.show', $client) }}" class="btn btn-primary">
                                <i class="fas fa-user me-2"></i>Torna al Profilo Cliente
                            </a>
                            <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-light">
                                <i class="fas fa-list me-2"></i>Tutte le Transazioni
                            </a>
                        </div>
                        <div>
                            <button class="btn btn-success" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Stampa Ricevuta
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Azioni Rapide Aggiuntive -->
            <div class="card bg-transparent border-light mt-3">
                <div class="card-header">
                    <h5><i class="fas fa-bolt me-2"></i>Azioni Rapide</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#newDepositModal">
                                <i class="fas fa-plus-circle me-2"></i>Nuovo Deposito
                            </button>
                        </div>
                        <div class="col-md-6 mb-2">
                            <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#newWithdrawalModal">
                                <i class="fas fa-minus-circle me-2"></i>Nuovo Prelievo
                            </button>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="{{ route('admin.transactions.create-transfer-form', $client) }}" class="btn btn-info w-100">
                                <i class="fas fa-paper-plane me-2"></i>Crea Bonifico
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-light w-100">
                                <i class="fas fa-users me-2"></i>Gestione Utenti
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuovo Deposito -->
<div class="modal fade" id="newDepositModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Nuovo Deposito per {{ $client->full_name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.deposit', $client) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="deposit_amount" class="form-label">Importo (€)</label>
                        <input type="number" class="form-control" id="deposit_amount" name="amount" step="0.01" min="0.01" max="100000" required>
                    </div>
                    <div class="mb-3">
                        <label for="deposit_description" class="form-label">Descrizione</label>
                        <input type="text" class="form-control" id="deposit_description" name="description" value="Deposito per {{ $client->full_name }}" required>
                    </div>
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle me-1"></i>Saldo attuale: €{{ number_format($new_balance, 2, ',', '.') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus-circle me-1"></i>Deposita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nuovo Prelievo -->
<div class="modal fade" id="newWithdrawalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Nuovo Prelievo per {{ $client->full_name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.withdrawal', $client) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_withdrawal_amount" class="form-label">Importo (€)</label>
                        <input type="number" class="form-control" id="new_withdrawal_amount" name="amount" step="0.01" min="0.01" max="{{ $new_balance }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_withdrawal_description" class="form-label">Descrizione</label>
                        <input type="text" class="form-control" id="new_withdrawal_description" name="description" value="Prelievo per {{ $client->full_name }}" required>
                    </div>
                    <div class="alert alert-warning">
                        <small><i class="fas fa-exclamation-triangle me-1"></i>Saldo disponibile: €{{ number_format($new_balance, 2, ',', '.') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-minus-circle me-1"></i>Preleva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .modal, .card-header .btn {
        display: none !important;
    }
    
    .container {
        width: 100% !important;
        max-width: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        background: white !important;
        color: black !important;
    }
    
    .alert-info {
        background-color: #f8f9fa !important;
        color: black !important;
        border: 1px solid #dee2e6 !important;
    }
}
</style>

<script>
// Validazione importo prelievo
document.getElementById('new_withdrawal_amount')?.addEventListener('input', function(e) {
    const maxAmount = {{ $new_balance }};
    const currentAmount = parseFloat(e.target.value);
    
    if (currentAmount > maxAmount) {
        e.target.setCustomValidity('Importo superiore al saldo disponibile');
    } else {
        e.target.setCustomValidity('');
    }
});
</script>
@endsection