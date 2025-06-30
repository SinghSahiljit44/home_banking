@extends('layouts.bootstrap')

@section('title', 'Report Transazioni')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-line me-2"></i>Report Transazioni</h2>
                <div>
                    <a href="{{ route('admin.reports.export.transactions', request()->query()) }}" class="btn btn-success me-2">
                        <i class="fas fa-download me-1"></i>Esporta CSV
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i>Report Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtri Avanzati -->
    <div class="card bg-transparent border-light mb-4">
        <div class="card-header">
            <h6><i class="fas fa-filter me-2"></i>Filtri Report</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.transactions') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Data Inizio</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Data Fine</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label">Tipo</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tutti</option>
                        <option value="transfer_in" {{ request('type') === 'transfer_in' ? 'selected' : '' }}>Bonifici Ricevuti</option>
                        <option value="transfer_out" {{ request('type') === 'transfer_out' ? 'selected' : '' }}>Bonifici Inviati</option>
                        <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Depositi</option>
                        <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Prelievi</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Stato</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tutti</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>In Sospeso</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completate</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Fallite</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filtra
                        </button>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label for="min_amount" class="form-label">Importo Minimo (€)</label>
                    <input type="number" class="form-control" id="min_amount" name="min_amount" 
                           value="{{ request('min_amount') }}" step="0.01" min="0">
                </div>
                <div class="col-md-3">
                    <label for="max_amount" class="form-label">Importo Massimo (€)</label>
                    <input type="number" class="form-control" id="max_amount" name="max_amount" 
                           value="{{ request('max_amount') }}" step="0.01" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Azioni</label>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.reports.transactions') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Reset
                        </a>
                        <button type="submit" name="export" value="1" class="btn btn-success btn-sm">
                            <i class="fas fa-download me-1"></i>Esporta Filtrati
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiche del Periodo -->
    @php
        $periodStats = [
            'total_transactions' => $transactions->total(),
            'total_volume' => $transactions->where('status', 'completed')->sum('amount'),
            'avg_transaction' => $transactions->where('status', 'completed')->count() > 0 ? $transactions->where('status', 'completed')->avg('amount') : 0,
            'pending_count' => $transactions->where('status', 'pending')->count(),
            'completed_count' => $transactions->where('status', 'completed')->count(),
            'failed_count' => $transactions->where('status', 'failed')->count(),
        ];
    @endphp

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-transparent border-primary text-center">
                <div class="card-body">
                    <i class="fas fa-hashtag fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary">{{ number_format($periodStats['total_transactions']) }}</h4>
                    <p class="mb-0">Transazioni Totali</p>
                    <small class="text-muted">Nel periodo selezionato</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-success text-center">
                <div class="card-body">
                    <i class="fas fa-euro-sign fa-2x text-success mb-2"></i>
                    <h4 class="text-success">€{{ number_format($periodStats['total_volume'], 2, ',', '.') }}</h4>
                    <p class="mb-0">Volume Totale</p>
                    <small class="text-muted">Solo transazioni completate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-info text-center">
                <div class="card-body">
                    <i class="fas fa-calculator fa-2x text-info mb-2"></i>
                    <h4 class="text-info">€{{ number_format($periodStats['avg_transaction'], 2, ',', '.') }}</h4>
                    <p class="mb-0">Importo Medio</p>
                    <small class="text-muted">Per transazione</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-warning text-center">
                <div class="card-body">
                    <i class="fas fa-chart-pie fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning">{{ $periodStats['completed_count'] > 0 ? number_format(($periodStats['completed_count'] / $periodStats['total_transactions']) * 100, 1) : 0 }}%</h4>
                    <p class="mb-0">Tasso Successo</p>
                    <small class="text-muted">Completate vs Totali</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafico Stati Transazioni -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h6><i class="fas fa-chart-pie me-2"></i>Distribuzione per Stato</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h6><i class="fas fa-chart-bar me-2"></i>Distribuzione per Tipo</h6>
                </div>
                <div class="card-body">
                    <canvas id="typeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabella Transazioni -->
    <div class="card bg-transparent border-light">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-table me-2"></i>Dettaglio Transazioni</h5>
                <div class="text-muted">
                    @if(request()->hasAny(['date_from', 'date_to', 'type', 'status', 'min_amount', 'max_amount']))
                        <span class="badge bg-info me-2">Filtrato</span>
                    @endif
                    {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} di {{ $transactions->total() }}
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-dark table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Data/Ora</th>
                                <th>Da</th>
                                <th>A</th>
                                <th class="text-end">Importo</th>
                                <th>Tipo</th>
                                <th>Stato</th>
                                <th>Descrizione</th>
                                <th class="text-center">Dettagli</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>
                                    <span class="font-monospace">#{{ $transaction->id }}</span>
                                </td>
                                <td>
                                    <div>{{ $transaction->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $transaction->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    @if($transaction->fromAccount)
                                        <div class="fw-bold">{{ $transaction->fromAccount->user->full_name }}</div>
                                        <small class="text-info">{{ $transaction->fromAccount->account_number }}</small>
                                    @else
                                        <span class="text-muted">Sistema/Esterno</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->toAccount)
                                        <div class="fw-bold">{{ $transaction->toAccount->user->full_name }}</div>
                                        <small class="text-info">{{ $transaction->toAccount->account_number }}</small>
                                    @else
                                        <span class="text-muted">Esterno/Sistema</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold 
                                        @if($transaction->type === 'deposit' || $transaction->type === 'transfer_in') text-success
                                        @else text-danger
                                        @endif">
                                        @if($transaction->type === 'deposit' || $transaction->type === 'transfer_in')+@else-@endif€{{ number_format($transaction->amount, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    @switch($transaction->type)
                                        @case('transfer_in')
                                            <span class="badge bg-success">Bonifico In</span>
                                            @break
                                        @case('transfer_out')
                                            <span class="badge bg-primary">Bonifico Out</span>
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
                                            <span class="badge bg-success">Completata</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-warning">In Sospeso</span>
                                            @break
                                        @case('failed')
                                            <span class="badge bg-danger">Fallita</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($transaction->status) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <span title="{{ $transaction->description }}">
                                        {{ Str::limit($transaction->description, 30) }}
                                    </span>
                                    @if(str_contains($transaction->description, '[STORNATA]'))
                                        <br><small class="text-warning"><i class="fas fa-undo"></i> Stornata</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.transactions.show', $transaction) }}" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="Visualizza Dettagli">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
                        Visualizzate {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} 
                        di {{ $transactions->total() }} transazioni
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($transactions->hasPages())
                            <small class="text-muted me-2">Pagina:</small>
                            <nav aria-label="Paginazione transazioni">
                                {{ $transactions->appends(request()->query())->links('pagination::bootstrap-4') }}
                            </nav>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nessuna transazione trovata</h5>
                    <p class="text-muted">Non ci sono transazioni che corrispondono ai filtri selezionati.</p>
                    @if(request()->hasAny(['date_from', 'date_to', 'type', 'status', 'min_amount', 'max_amount']))
                        <a href="{{ route('admin.reports.transactions') }}" class="btn btn-primary">
                            <i class="fas fa-eye me-1"></i>Visualizza tutte le transazioni
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Riepilogo Rapido -->
    @if($transactions->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-transparent border-secondary">
                    <div class="card-header">
                        <h6><i class="fas fa-clipboard-list me-2"></i>Riepilogo Rapido</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="border-end">
                                    <h5 class="text-success">{{ $periodStats['completed_count'] }}</h5>
                                    <small class="text-muted">Completate</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="border-end">
                                    <h5 class="text-warning">{{ $periodStats['pending_count'] }}</h5>
                                    <small class="text-muted">In Sospeso</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="border-end">
                                    <h5 class="text-danger">{{ $periodStats['failed_count'] }}</h5>
                                    <small class="text-muted">Fallite</small>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <h5 class="text-info">
                                    @php
                                        $deposits = $transactions->where('type', 'deposit')->where('status', 'completed')->sum('amount');
                                        $withdrawals = $transactions->where('type', 'withdrawal')->where('status', 'completed')->sum('amount');
                                        $netFlow = $deposits - $withdrawals;
                                    @endphp
                                    <span class="{{ $netFlow >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $netFlow >= 0 ? '+' : '' }}€{{ number_format($netFlow, 2, ',', '.') }}
                                    </span>
                                </h5>
                                <small class="text-muted">Flusso Netto</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dati per i grafici
    const statusData = {
        completed: {{ $periodStats['completed_count'] }},
        pending: {{ $periodStats['pending_count'] }},
        failed: {{ $periodStats['failed_count'] }}
    };

    const typeData = {
        transfer_in: {{ $transactions->where('type', 'transfer_in')->count() }},
        transfer_out: {{ $transactions->where('type', 'transfer_out')->count() }},
        deposit: {{ $transactions->where('type', 'deposit')->count() }},
        withdrawal: {{ $transactions->where('type', 'withdrawal')->count() }}
    };

    // Grafico Stati
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completate', 'In Sospeso', 'Fallite'],
            datasets: [{
                data: [statusData.completed, statusData.pending, statusData.failed],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderColor: ['#1e7e34', '#e0a800', '#c82333'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: 'white',
                        padding: 20
                    }
                }
            }
        }
    });

    // Grafico Tipi
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'bar',
        data: {
            labels: ['Bonifici In', 'Bonifici Out', 'Depositi', 'Prelievi'],
            datasets: [{
                label: 'Numero Transazioni',
                data: [typeData.transfer_in, typeData.transfer_out, typeData.deposit, typeData.withdrawal],
                backgroundColor: ['#28a745', '#007bff', '#17a2b8', '#ffc107'],
                borderColor: ['#1e7e34', '#0056b3', '#117a8b', '#e0a800'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: 'white'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: 'white'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                }
            }
        }
    });

    // Auto-imposta data di fine quando si seleziona data di inizio
    document.getElementById('date_from').addEventListener('change', function() {
        const dateTo = document.getElementById('date_to');
        if (!dateTo.value && this.value) {
            dateTo.value = this.value;
        }
    });

    // Validazione importi
    const minAmount = document.getElementById('min_amount');
    const maxAmount = document.getElementById('max_amount');
    
    minAmount.addEventListener('change', function() {
        if (maxAmount.value && parseFloat(this.value) > parseFloat(maxAmount.value)) {
            maxAmount.value = this.value;
        }
    });
    
    maxAmount.addEventListener('change', function() {
        if (minAmount.value && parseFloat(this.value) < parseFloat(minAmount.value)) {
            minAmount.value = this.value;
        }
    });
});
</script>

<style>
.border-end {
    border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
}

@media (max-width: 768px) {
    .border-end {
        border-right: none !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
    }
}
</style>
@endsection