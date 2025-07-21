@extends('layouts.bootstrap')

@section('title', 'Transazioni Clienti')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-exchange-alt me-2"></i>Transazioni dei Tuoi Clienti</h2>
                <a href="{{ route('dashboard.employee') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard Employee
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistiche Rapide -->
    <div class="row mb-4 justify-content-center">
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-list-alt fa-2x text-info mb-2"></i>
                    <h4 class="text-info">{{ $transactions->total() }}</h4>
                    <p class="mb-0">Transazioni Totali</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-calendar-day fa-2x text-success mb-2"></i>
                    <h4 class="text-success">
                        {{ $transactions->where('created_at', '>=', today())->count() }}
                    </h4>
                    <p class="mb-0">Oggi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary">{{ $assignedClients->count() }}</h4>
                    <p class="mb-0">Clienti Assegnati</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtri di Ricerca -->
    <div class="card bg-transparent border-light mb-4">
        <div class="card-header">
            <h6><i class="fas fa-filter me-2"></i>Filtri di Ricerca</h6>
        </div>
        <div class="card-body">
            <div id="validation-errors">
                <div id="date-error" class="alert alert-danger d-none" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    La data "Dal" non può essere successiva alla data "Al"
                </div>
            </div>  
            <form method="GET" action="{{ route('employee.transactions.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="client_id" class="form-label">Cliente</label>
                    <select class="form-select" id="client_id" name="client_id">
                        <option value="">Tutti i clienti</option>
                        @foreach($assignedClients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Data Da</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}">
                </div>
                
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Data A</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}">
                </div>
                
                <div class="col-md-3">
                    <label for="type" class="form-label">Tipo</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tutti i tipi</option>
                        <option value="transfer_in" {{ request('type') === 'transfer_in' ? 'selected' : '' }}>Bonifico Ricevuto</option>
                        <option value="transfer_out" {{ request('type') === 'transfer_out' ? 'selected' : '' }}>Bonifico Inviato</option>
                        <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposito</option>
                        <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Prelievo</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary" id="filter-btn">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('employee.transactions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista Transazioni -->
    <div class="card bg-transparent border-light">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list me-2"></i>Transazioni ({{ $transactions->total() }})</h5>
            </div>
        </div>
        <div class="card-body">
            @forelse($transactions as $transaction)
                <div class="card bg-dark border-secondary mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <!-- Data e Riferimento -->
                            <div class="col-md-2">
                                <div class="text-center">
                                    <div class="fw-bold">{{ $transaction->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                                    <br>
                                    <small class="font-monospace text-info">{{ $transaction->reference_code }}</small>
                                </div>
                            </div>
                            
                            <!-- Cliente e Tipo -->
                            <div class="col-md-3">
                                @if($transaction->fromAccount && $transaction->fromAccount->user)
                                    @php
                                        $clientUser = null;
                                        // Determina quale utente è il cliente assegnato
                                        if($transaction->fromAccount->user->isClient() && Auth::user()->canManageClient($transaction->fromAccount->user)) {
                                            $clientUser = $transaction->fromAccount->user;
                                        } elseif($transaction->toAccount && $transaction->toAccount->user && $transaction->toAccount->user->isClient() && Auth::user()->canManageClient($transaction->toAccount->user)) {
                                            $clientUser = $transaction->toAccount->user;
                                        }
                                    @endphp
                                    
                                    @if($clientUser)
                                        <div class="fw-bold">{{ $clientUser->full_name }}</div>
                                        <small class="text-muted">{{ $clientUser->username }}</small>
                                    @else
                                        <div class="text-muted">Cliente non assegnato</div>
                                    @endif
                                @else
                                    <div class="text-muted">Sistema</div>
                                @endif
                                
                                <!-- Tipo Transazione -->
                                <div class="mt-1">
                                    @if($transaction->type === 'transfer_in')
                                        <span class="badge bg-success">Bonifico Ricevuto</span>
                                    @elseif($transaction->type === 'transfer_out')
                                        <span class="badge bg-primary">Bonifico Inviato</span>
                                    @elseif($transaction->type === 'deposit')
                                        <span class="badge bg-info">Deposito</span>
                                    @elseif($transaction->type === 'withdrawal')
                                        <span class="badge bg-warning">Prelievo</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($transaction->type) }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Descrizione -->
                            <div class="col-md-4">
                                <div>{{ Str::limit($transaction->description, 60) }}</div>
                                @if($transaction->toAccount && !$transaction->fromAccount)
                                    <small class="text-muted">Deposito esterno</small>
                                @elseif($transaction->fromAccount && !$transaction->toAccount)
                                    <small class="text-muted">Bonifico esterno</small>
                                @elseif($transaction->fromAccount && $transaction->toAccount)
                                    <small class="text-muted">Bonifico interno</small>
                                @endif
                            </div>
                            
                            <!-- Importo e Stato -->
                            <div class="col-md-2 text-center">
                                @php
                                    $isClientTransaction = false;
                                    $isIncoming = false;
                                    
                                    // Verifica se è una transazione di un cliente assegnato
                                    if($transaction->fromAccount && $transaction->fromAccount->user && Auth::user()->canManageClient($transaction->fromAccount->user)) {
                                        $isClientTransaction = true;
                                        $isIncoming = false; // È in uscita dal cliente
                                    } elseif($transaction->toAccount && $transaction->toAccount->user && Auth::user()->canManageClient($transaction->toAccount->user)) {
                                        $isClientTransaction = true;
                                        $isIncoming = true; // È in entrata al cliente
                                    }
                                @endphp
                                
                                @if($isClientTransaction)
                                    <div class="h5 {{ $isIncoming ? 'text-success' : 'text-danger' }}">
                                        {{ $isIncoming ? '+' : '-' }}€{{ number_format($transaction->amount, 2, ',', '.') }}
                                    </div>
                                @else
                                    <div class="h5 text-info">
                                        €{{ number_format($transaction->amount, 2, ',', '.') }}
                                    </div>
                                @endif
                                
                                <div>
                                    @if($transaction->status === 'completed')
                                        <span class="badge bg-success">Completato</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($transaction->status) }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Azioni -->
                            <div class="col-md-1">
                                <div class="dropdown">
                                    <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" 
                                            data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark">
                                        <li>
                                            <button class="dropdown-item" onclick="showTransactionDetails({{ $transaction->id }})">
                                                <i class="fas fa-eye me-2"></i>Dettagli
                                            </button>
                                        </li>
                                        @if($transaction->status === 'pending')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-warning" href="#" 
                                                   onclick="alert('Funzione disponibile solo per Admin')">
                                                    <i class="fas fa-clock me-2"></i>Gestisci Stato
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nessuna transazione trovata</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['client_id', 'date_from', 'date_to', 'type']))
                            Non ci sono transazioni che corrispondono ai filtri selezionati.
                        @else
                            I tuoi clienti non hanno ancora effettuato transazioni.
                        @endif
                    </p>
                    @if(request()->hasAny(['client_id', 'date_from', 'date_to', 'type']))
                        <a href="{{ route('employee.transactions.index') }}" class="btn btn-outline-light">
                            <i class="fas fa-filter me-1"></i>Rimuovi Filtri
                        </a>
                    @endif
                </div>
            @endforelse

            <!-- Paginazione -->
            @if($transactions->count() > 0)
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Visualizzati {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} 
                        di {{ $transactions->total() }} transazioni
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($transactions->hasPages())
                            <small class="text-muted me-2">Pagina:</small>
                            <nav aria-label="Paginazione transazioni">
                                {{ $transactions->links('pagination::bootstrap-4') }}
                            </nav>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Dettagli Transazione -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Dettagli Transazione</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="transactionDetails">
                <!-- Contenuto caricato dinamicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>

<script>

function showTransactionDetails(transactionId) {
    // Mostra loading nel modal
    const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
    const modalBody = document.getElementById('transactionDetails');
    
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Caricamento...</span>
            </div>
            <p class="mt-2">Caricamento dettagli transazione...</p>
        </div>
    `;
    
    modal.show();

    // Carica i dettagli via AJAX
    fetch(`/employee/transactions/details/${transactionId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Errore nel caricamento dei dettagli');
            }
            return response.text();
        })
        .then(html => {
            modalBody.innerHTML = html;
        })
        .catch(error => {
            console.error('Errore:', error);
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Errore nel caricamento dei dettagli della transazione.
                    <br><small>Puoi provare a <a href="/employee/transactions/details/${transactionId}" target="_blank">aprire i dettagli in una nuova pagina</a>.</small>
                </div>
            `;
        });
}

// Funzione per aprire i dettagli in una nuova finestra/tab
function openTransactionInNewTab(transactionId) {
    window.open(`/employee/transactions/details/${transactionId}`, '_blank');
}

// Funzioni di validazione date
function validateDates() {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    const dateErrorDiv = document.getElementById('date-error');
    
    if (dateFrom && dateTo && dateFrom > dateTo) {
        dateErrorDiv.classList.remove('d-none');
        return false;
    } else {
        dateErrorDiv.classList.add('d-none');
        return true;
    }
}

function validateForm() {
    const dateValid = validateDates();
    const submitBtn = document.getElementById('filter-btn');
    
    if (dateValid) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('disabled');
        return true;
    } else {
        submitBtn.disabled = true;
        submitBtn.classList.add('disabled');
        return false;
    }
}

// Auto-submit form quando cambiano i filtri (escluse le date per evitare conflitti con la validazione)
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('form[action*="transactions"]');
    if (filterForm) {
        const selectInputs = filterForm.querySelectorAll('select');
        
        selectInputs.forEach(input => {
            input.addEventListener('change', function() {
                // Auto-submit dopo un breve delay per UX migliore
                setTimeout(() => {
                    if (validateForm()) {
                        filterForm.submit();
                    }
                }, 300);
            });
        });
    }
    
    // Validazione su cambio delle date
    document.getElementById('date_from').addEventListener('change', function() {
        const dateTo = document.getElementById('date_to');
        if (!dateTo.value && this.value) {
            dateTo.value = this.value;
        }
        validateForm();
    });

    document.getElementById('date_to').addEventListener('change', validateForm);
    
    // Previeni submit se la validazione fallisce
    filterForm.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });
});

// Evidenzia le transazioni di oggi
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toLocaleDateString('it-IT');
    const transactionCards = document.querySelectorAll('.card.bg-dark');
    
    transactionCards.forEach(card => {
        const dateElement = card.querySelector('.fw-bold');
        if (dateElement && dateElement.textContent.includes(today.split('/').reverse().join('/'))) {
            card.classList.add('border-warning');
        }
    });
});

function copyReferenceCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        // Mostra feedback all'utente
        const toast = document.createElement('div');
        toast.className = 'toast position-fixed bottom-0 end-0 m-3';
        toast.innerHTML = `
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Copiato!</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                Codice riferimento copiato negli appunti.
            </div>
        `;
        document.body.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Rimuovi il toast dopo che si è nascosto
        toast.addEventListener('hidden.bs.toast', function() {
            document.body.removeChild(toast);
        });
    }).catch(function(err) {
        console.error('Errore nella copia:', err);
    });
}
</script>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}

/* Migliora la leggibilità delle transazioni */
.card.bg-dark:hover {
    background-color: rgba(52, 58, 64, 0.8) !important;
    transform: translateY(-1px);
    transition: all 0.2s ease-in-out;
}

/* Evidenzia le transazioni di oggi */
.card.border-warning {
    box-shadow: 0 0 10px rgba(255, 193, 7, 0.3);
}

/* Styling per importi */
.h5.text-success, .h5.text-danger, .h5.text-info {
    font-weight: bold;
    margin-bottom: 0.25rem;
}

/* Responsive per mobile */
@media (max-width: 768px) {
    .card .row > div {
        margin-bottom: 0.5rem;
    }
    
    .card .row > div:last-child {
        margin-bottom: 0;
    }
}
</style>
@endsection