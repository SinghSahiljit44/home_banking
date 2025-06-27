@extends('layouts.bootstrap')

@section('title', 'Dettagli Utente')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user me-2"></i>Dettagli Utente: {{ $user->full_name }}</h2>
                <div>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i>Modifica
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i>Torna alla Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Informazioni Utente -->
        <div class="col-lg-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-id-card me-2"></i>Informazioni Personali</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nome Completo:</strong> {{ $user->full_name }}</p>
                            <p><strong>Username:</strong> {{ $user->username }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Telefono:</strong> {{ $user->phone ?: 'Non specificato' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Ruolo:</strong> 
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'employee' ? 'warning' : 'success') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </p>
                            <p><strong>Stato:</strong> 
                                <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $user->is_active ? 'Attivo' : 'Sospeso' }}
                                </span>
                            </p>
                            <p><strong>Registrato:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Ultimo Aggiornamento:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @if($user->address)
                        <div class="row">
                            <div class="col-12">
                                <p><strong>Indirizzo:</strong> {{ $user->address }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Informazioni Conto (se cliente) -->
            @if($user->isClient() && $user->account)
                <div class="card bg-transparent border-light mt-3">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-university me-2"></i>Conto Corrente</h5>
                            <form method="POST" action="{{ route('admin.users.toggle-account', $user) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $user->account->is_active ? 'btn-warning' : 'btn-success' }}">
                                    <i class="fas {{ $user->account->is_active ? 'fa-lock' : 'fa-unlock' }} me-1"></i>
                                    {{ $user->account->is_active ? 'Blocca' : 'Sblocca' }}
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Numero Conto:</strong> {{ $user->account->account_number }}</p>
                                <p><strong>IBAN:</strong> <span class="font-monospace">{{ $user->account->iban }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Saldo:</strong> 
                                    <span class="h5 text-success">€{{ number_format($user->account->balance, 2, ',', '.') }}</span>
                                </p>
                                <p><strong>Stato Conto:</strong> 
                                    <span class="badge {{ $user->account->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->account->is_active ? 'Attivo' : 'Bloccato' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($user->isClient() && !$user->account)
                <div class="card bg-transparent border-warning mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Nessun Conto Associato</h5>
                    </div>
                    <div class="card-body">
                        <p>Questo cliente non ha ancora un conto corrente associato.</p>
                        <form method="POST" action="{{ route('admin.users.create-account', $user) }}">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>Crea Conto
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Azioni e Statistiche -->
        <div class="col-lg-4">
            <!-- Azioni Rapide -->
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-cogs me-2"></i>Azioni</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Modifica Utente
                        </a>
                        
                        @if($user->account)
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#depositModal">
                                <i class="fas fa-plus-circle me-2"></i>Deposita Fondi
                            </button>
                        @endif
                        
                        @if(!$user->isAdmin())
                            <button class="btn btn-danger" onclick="confirmDelete({{ $user->id }})">
                                <i class="fas fa-user-slash me-2"></i>Disattiva Utente
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistiche -->
            @if($transactionStats)
                <div class="card bg-transparent border-light mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar me-2"></i>Statistiche Transazioni</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Totale Transazioni:</strong> {{ $transactionStats['total_transactions'] }}</p>
                        <p><strong>Entrate Totali:</strong> 
                            <span class="text-success">€{{ number_format($transactionStats['total_incoming'], 2, ',', '.') }}</span>
                        </p>
                        <p><strong>Uscite Totali:</strong> 
                            <span class="text-danger">€{{ number_format($transactionStats['total_outgoing'], 2, ',', '.') }}</span>
                        </p>
                        @if($transactionStats['last_transaction'])
                            <p><strong>Ultima Transazione:</strong> {{ $transactionStats['last_transaction']->created_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Transazioni Recenti -->
    @if($user->account && $user->account->allTransactions()->exists())
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-transparent border-light">
                    <div class="card-header">
                        <h5><i class="fas fa-history me-2"></i>Transazioni Recenti</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Tipo</th>
                                        <th>Descrizione</th>
                                        <th class="text-end">Importo</th>
                                        <th>Stato</th>
                                        <th>Riferimento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->account->allTransactions()->take(10)->get() as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($transaction->from_account_id === $user->account->id)
                                                <span class="badge bg-primary">Uscita</span>
                                            @else
                                                <span class="badge bg-success">Entrata</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($transaction->description, 40) }}</td>
                                        <td class="text-end">
                                            @if($transaction->from_account_id === $user->account->id)
                                                <span class="text-danger">-€{{ number_format($transaction->amount, 2, ',', '.') }}</span>
                                            @else
                                                <span class="text-success">+€{{ number_format($transaction->amount, 2, ',', '.') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td><small class="font-monospace">{{ $transaction->reference_code }}</small></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal Deposito -->
@if($user->account)
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Deposita Fondi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.deposit', $user) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Importo (€)</label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrizione</label>
                        <input type="text" class="form-control" id="description" name="description" value="Deposito amministrativo" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">Deposita</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal Conferma Eliminazione -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Conferma Disattivazione</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Sei sicuro di voler disattivare questo utente?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>L'utente non potrà più accedere al sistema.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Disattiva</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(userId) {
    const form = document.getElementById('deleteForm');
    form.action = `/admin/users/${userId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endsection
