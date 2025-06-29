@extends('layouts.bootstrap')

@section('title', 'Dettagli Cliente')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user me-2"></i>{{ $client->full_name }}</h2>
                <div>
                    <a href="{{ route('employee.clients.edit', $client) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i>Modifica
                    </a>
                    <a href="{{ route('employee.clients.index') }}" class="btn btn-outline-light">
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

    @if (session('temp_password'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-key me-2"></i>
            <strong>Password temporanea generata:</strong> <code>{{ session('temp_password') }}</code>
            <br><small>Comunicala al cliente e chiedigli di cambiarla al primo accesso.</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Informazioni Cliente -->
        <div class="col-lg-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-id-card me-2"></i>Informazioni Personali</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nome Completo:</strong> {{ $client->full_name }}</p>
                            <p><strong>Username:</strong> {{ $client->username }}</p>
                            <p><strong>Email:</strong> {{ $client->email }}</p>
                            <p><strong>Telefono:</strong> {{ $client->phone ?: 'Non specificato' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Stato:</strong> 
                                <span class="badge {{ $client->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $client->is_active ? 'Attivo' : 'Sospeso' }}
                                </span>
                            </p>
                            <p><strong>Registrato:</strong> {{ $client->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Ultimo Aggiornamento:</strong> {{ $client->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @if($client->address)
                        <div class="row">
                            <div class="col-12">
                                <p><strong>Indirizzo:</strong> {{ $client->address }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Informazioni Conto -->
            @if($client->account)
                <div class="card bg-transparent border-light mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-university me-2"></i>Conto Corrente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Numero Conto:</strong> {{ $client->account->account_number }}</p>
                                <p><strong>IBAN:</strong> <span class="font-monospace">{{ $client->account->iban }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Saldo:</strong> 
                                    <span class="h5 text-success">€{{ number_format($client->account->balance, 2, ',', '.') }}</span>
                                </p>
                                <p><strong>Stato Conto:</strong> 
                                    <span class="badge {{ $client->account->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $client->account->is_active ? 'Attivo' : 'Bloccato' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card bg-transparent border-warning mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Nessun Conto Associato</h5>
                    </div>
                    <div class="card-body">
                        <p>Questo cliente non ha ancora un conto corrente associato.</p>
                        <form method="POST" action="{{ route('employee.clients.create-account', $client) }}">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>Crea Conto
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Transazioni Recenti -->
            @if($recentTransactions->count() > 0)
                <div class="card bg-transparent border-light mt-3">
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
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($transaction->from_account_id === $client->account->id)
                                                <span class="badge bg-primary">Uscita</span>
                                            @else
                                                <span class="badge bg-success">Entrata</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($transaction->description, 40) }}</td>
                                        <td class="text-end">
                                            @if($transaction->from_account_id === $client->account->id)
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
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Azioni e Statistiche -->
        <div class="col-lg-4">
            <!-- Statistiche -->
            @if($clientStats)
                <div class="card bg-transparent border-light">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar me-2"></i>Statistiche</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Totale Transazioni:</strong> {{ $clientStats['total_transactions'] }}</p>
                        <p><strong>Entrate Totali:</strong> 
                            <span class="text-success">€{{ number_format($clientStats['total_incoming'], 2, ',', '.') }}</span>
                        </p>
                        <p><strong>Uscite Totali:</strong> 
                            <span class="text-danger">€{{ number_format($clientStats['total_outgoing'], 2, ',', '.') }}</span>
                        </p>
                        <p><strong>Saldo Corrente:</strong> 
                            <span class="text-info">€{{ number_format($clientStats['current_balance'], 2, ',', '.') }}</span>
                        </p>
                        @if($clientStats['last_transaction'])
                            <p><strong>Ultima Transazione:</strong> {{ $clientStats['last_transaction']->created_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Azioni Rapide -->
            <div class="card bg-transparent border-light mt-3">
                <div class="card-header">
                    <h5><i class="fas fa-cogs me-2"></i>Azioni</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('employee.clients.edit', $client) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Modifica Cliente
                        </a>
                        
                        @if($client->account)
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#depositModal">
                                <i class="fas fa-plus-circle me-2"></i>Deposita Fondi
                            </button>
                            
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#transferModal">
                                <i class="fas fa-paper-plane me-2"></i>Crea Bonifico
                            </button>
                        @endif
                        
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#passwordModal">
                            <i class="fas fa-key me-2"></i>Reset Password
                        </button>
                        
                        <button class="btn btn-{{ $client->is_active ? 'warning' : 'success' }}" 
                                onclick="toggleClientStatus({{ $client->id }})">
                            <i class="fas fa-{{ $client->is_active ? 'lock' : 'unlock' }} me-2"></i>
                            {{ $client->is_active ? 'Sospendi' : 'Attiva' }} Cliente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Deposito -->
@if($client->account)
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Deposita Fondi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('employee.clients.deposit', $client) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Importo (€)</label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrizione</label>
                        <input type="text" class="form-control" id="description" name="description" value="Deposito per {{ $client->full_name }}" required>
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

<!-- Modal Bonifico -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Crea Bonifico per {{ $client->full_name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('employee.clients.transfer', $client) }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="recipient_iban" class="form-label">IBAN Beneficiario</label>
                            <input type="text" class="form-control" id="recipient_iban" name="recipient_iban" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="beneficiary_name" class="form-label">Nome Beneficiario</label>
                            <input type="text" class="form-control" id="beneficiary_name" name="beneficiary_name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="transfer_amount" class="form-label">Importo (€)</label>
                            <input type="number" class="form-control" id="transfer_amount" name="amount" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="transfer_description" class="form-label">Causale</label>
                        <input type="text" class="form-control" id="transfer_description" name="description" required>
                    </div>
                    <div class="alert alert-info">
                        <small>Saldo disponibile: €{{ number_format($client->account->balance, 2, ',', '.') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">Esegui Bonifico</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal Reset Password -->
<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password per {{ $client->full_name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('employee.clients.reset-password', $client) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Verrà generata una nuova password temporanea per il cliente.
                    </div>
                    <p>Confermi di voler resettare la password per <strong>{{ $client->full_name }}</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleClientStatus(clientId) {
    if (confirm('Sei sicuro di voler cambiare lo stato di questo cliente?')) {
        // Crea form temporaneo per inviare richiesta POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/employee/clients/${clientId}/toggle-status`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

// Formatta IBAN nel modal bonifico
document.getElementById('recipient_iban')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '').toUpperCase();
    let formatted = value.replace(/(.{4})/g, '$1 ').trim();
    e.target.value = formatted;
});
</script>
@endsection