@extends('layouts.bootstrap')

@section('title', 'Il mio Conto')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-university me-2"></i>Il mio Conto Corrente</h2>
                <a href="{{ route('dashboard.cliente') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Torna alla Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Informazioni Conto -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-credit-card me-2"></i>Dettagli Conto</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Intestatario:</strong> {{ Auth::user()->full_name }}</p>
                            <p><strong>Numero Conto:</strong> {{ $account->account_number }}</p>
                            <p><strong>IBAN:</strong> <span class="font-monospace">{{ $account->iban }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Saldo Disponibile:</strong></p>
                            <h3 class="text-success">€{{ number_format($account->balance, 2, ',', '.') }}</h3>
                            <p><strong>Stato:</strong> 
                                <span class="badge {{ $account->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $account->is_active ? 'Attivo' : 'Sospeso' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h6><i class="fas fa-chart-line me-2"></i>Statistiche Periodo</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Totale Transazioni:</strong> {{ $stats['total_transactions'] }}</p>
                    <p class="mb-2"><strong>Entrate:</strong> 
                        <span class="text-success">€{{ number_format($stats['total_incoming'], 2, ',', '.') }}</span>
                    </p>
                    <p class="mb-2"><strong>Uscite:</strong> 
                        <span class="text-danger">€{{ number_format($stats['total_outgoing'], 2, ',', '.') }}</span>
                    </p>
                    <p class="mb-0"><strong>Media Transazione:</strong> 
                        €{{ number_format($stats['average_transaction'], 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtri -->
    <div class="card bg-transparent border-light mb-4">
        <div class="card-header">
            <h6><i class="fas fa-filter me-2"></i>Filtri Ricerca</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('client.account.show') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Dal</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Al</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label">Tipo</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tutti</option>
                        <option value="transfer_in" {{ $type === 'transfer_in' ? 'selected' : '' }}>Bonifici Ricevuti</option>
                        <option value="transfer_out" {{ $type === 'transfer_out' ? 'selected' : '' }}>Bonifici Inviati</option>
                        <option value="deposit" {{ $type === 'deposit' ? 'selected' : '' }}>Depositi</option>
                        <option value="withdrawal" {{ $type === 'withdrawal' ? 'selected' : '' }}>Prelievi</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="min_amount" class="form-label">Importo Min</label>
                    <input type="number" class="form-control" id="min_amount" name="min_amount" step="0.01" value="{{ $minAmount }}">
                </div>
                <div class="col-md-2">
                    <label for="max_amount" class="form-label">Importo Max</label>
                    <input type="number" class="form-control" id="max_amount" name="max_amount" step="0.01" value="{{ $maxAmount }}">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filtra
                    </button>
                    <a href="{{ route('client.account.show') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                    <a href="{{ route('client.account.export-csv', request()->query()) }}" class="btn btn-success">
                        <i class="fas fa-download me-1"></i>Esporta CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista Movimenti -->
    <div class="card bg-transparent border-light">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list me-2"></i>Movimenti del Conto</h5>
                <div>
                    <select class="form-select form-select-sm" onchange="changePerPage(this.value)">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 per pagina</option>
                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 per pagina</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 per pagina</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-dark table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descrizione</th>
                                <th>Tipo</th>
                                <th class="text-end">Importo</th>
                                <th>Stato</th>
                                <th>Riferimento</th>
                                <th class="text-center">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>
                                    <small>{{ $transaction->created_at->format('d/m/Y') }}</small><br>
                                    <small class="text-muted">{{ $transaction->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ Str::limit($transaction->description, 50) }}</div>
                                    @if($transaction->from_account_id !== $account->id && $transaction->fromAccount)
                                        <small class="text-info">Da: {{ $transaction->fromAccount->user->full_name }}</small>
                                    @elseif($transaction->to_account_id !== $account->id && $transaction->toAccount)
                                        <small class="text-info">A: {{ $transaction->toAccount->user->full_name }}</small>
                                    @endif
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
                                <td class="text-end">
                                    @if($transaction->from_account_id === $account->id)
                                        <span class="text-danger fw-bold">
                                            <i class="fas fa-arrow-down me-1"></i>
                                            -€{{ number_format($transaction->amount, 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-success fw-bold">
                                            <i class="fas fa-arrow-up me-1"></i>
                                            +€{{ number_format($transaction->amount, 2, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @switch($transaction->status)
                                        @case('completed')
                                            <span class="badge bg-success">Completato</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-warning">In Elaborazione</span>
                                            @break
                                        @case('failed')
                                            <span class="badge bg-danger">Fallito</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($transaction->status) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <small class="font-monospace text-muted">{{ $transaction->reference_code }}</small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('client.account.transaction.show', $transaction->id) }}" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="Visualizza dettagli">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginazione -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} results
                    </div>
                    <div>
                        {{ $transactions->links() }}
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

<script>
function changePerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', value);
    window.location.href = url.toString();
}

// Auto-imposta data di fine quando si seleziona data di inizio
document.getElementById('date_from').addEventListener('change', function() {
    const dateTo = document.getElementById('date_to');
    if (!dateTo.value && this.value) {
        dateTo.value = this.value;
    }
});
</script>
@endsection