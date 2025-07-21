@extends('layouts.bootstrap')

@section('title', 'Gestione Transazioni')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-exchange-alt me-2"></i>Gestione Transazioni</h2>
                <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard Admin
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiche -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-exchange-alt fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary">{{ number_format($stats['total_transactions']) }}</h4>
                    <p class="mb-0">Transazioni Totali</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning">{{ number_format($stats['pending_transactions']) }}</h4>
                    <p class="mb-0">In Sospeso</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h4 class="text-success">{{ number_format($stats['completed_transactions']) }}</h4>
                    <p class="mb-0">Completate</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-euro-sign fa-2x text-info mb-2"></i>
                    <h4 class="text-info">€{{ number_format($stats['volume_today'], 2, ',', '.') }}</h4>
                    <p class="mb-0">Volume Oggi</p>
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
            <form method="GET" action="{{ route('admin.transactions.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Cerca</label>
                    <input type="text" class="form-control" id="search" name="search" 
                        value="{{ $search }}" placeholder="Descrizione, codice, utente...">
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Dal</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Al</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label">Tipo</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tutti</option>
                        <option value="transfer_in" {{ $type === 'transfer_in' ? 'selected' : '' }}>Bonifici Ricevuti</option>
                        <option value="transfer_out" {{ $type === 'transfer_out' ? 'selected' : '' }}>Bonifici Inviati</option>
                        <option value="deposit" {{ $type === 'deposit' ? 'selected' : '' }}>Depositi</option>
                        <option value="withdrawal" {{ $type === 'withdrawal' ? 'selected' : '' }}>Prelievi</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" id="filter-btn">
                        <i class="fas fa-search me-1"></i>Filtra
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Reset Filtri
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
                <small class="text-muted">
                    Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} results
                </small>
            </div>
        </div>
        <div class="card-body">
            @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-dark table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Data/Ora</th>
                                <th>Da</th>
                                <th>A</th>
                                <th class="text-end">Importo</th>
                                <th>Tipo</th>
                                <th>Stato</th>
                                <th>Riferimento</th>
                                <th class="text-center">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>
                                    <div>{{ $transaction->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $transaction->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    @if($transaction->fromAccount)
                                        <div class="fw-bold">{{ $transaction->fromAccount->user->full_name }}</div>
                                        <small class="text-info">{{ $transaction->fromAccount->account_number }}</small>
                                    @else
                                        <span class="text-muted">Sistema</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->toAccount)
                                        <div class="fw-bold">{{ $transaction->toAccount->user->full_name }}</div>
                                        <small class="text-info">{{ $transaction->toAccount->account_number }}</small>
                                    @else
                                        <span class="text-muted">Esterno</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold">€{{ number_format($transaction->amount, 2, ',', '.') }}</span>
                                </td>
                                <td>
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
                                </td>
                                <td>
                                    @switch($transaction->status)
                                        @case('completed')
                                            <span class="badge bg-success">Completato</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-warning">In Sospeso</span>
                                            @break
                                        @case('failed')
                                            <span class="badge bg-danger">Fallito</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($transaction->status) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <small class="font-monospace">{{ $transaction->reference_code }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.transactions.show', $transaction) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="Visualizza">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($transaction->status === 'pending')
                                            <form method="POST" action="{{ route('admin.transactions.approve', $transaction) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        onclick="return confirm('Confermi l\'approvazione di questa transazione?')"
                                                        title="Approva">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" action="{{ route('admin.transactions.reject', $transaction) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Confermi il rifiuto di questa transazione? L\'importo sarà rimborsato se necessario.')"
                                                        title="Rifiuta">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($transaction->status === 'completed' && !str_contains($transaction->description, '[STORNATA]'))
                                            <form method="POST" action="{{ route('admin.transactions.reverse', $transaction) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-warning" 
                                                        onclick="return confirm('ATTENZIONE: Questa operazione creerà una transazione di storno. Sei sicuro?')"
                                                        title="Storna">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginazione -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Visualizzati {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} 
                        di {{ $transactions->total() }} movimenti
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($transactions->hasPages())
                            <small class="text-muted me-2">Pagina:</small>
                            <nav aria-label="Paginazione movimenti">
                                {{ $transactions->links('pagination::bootstrap-4') }}
                            </nav>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nessun movimento trovato</h5>
                    <p class="text-muted">Non ci sono transazioni che corrispondono ai filtri selezionati.</p>
                    @if(request()->hasAny(['date_from', 'date_to', 'type', 'min_amount', 'max_amount']))
                        <a href="{{ route('client.account.show') }}" class="btn btn-primary">
                            <i class="fas fa-eye me-1"></i>Visualizza tutti i movimenti
                        </a>
                    @endif
                </div>
            @endif
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // VALIDAZIONI DATE
    
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

    // Auto-submit form quando cambiano i filtri 
    const filterForm = document.querySelector('form[action*="transactions.index"]');
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

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>

@endsection