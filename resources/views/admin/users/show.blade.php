@extends('layouts.bootstrap')

@section('title', 'Dettagli Utente')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user me-2"></i>Dettagli Utente: {{ $user->full_name }}</h2>
                <div>
                    @if(!$user->isAdmin() || $user->id === Auth::id())
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit me-1"></i>Modifica
                        </a>
                    @endif
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

    <!-- Alert per password generata -->
    @if (session('generated_password'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-key me-2"></i>
            <strong>Password generata automaticamente:</strong> 
            <code class="bg-dark text-warning p-1 rounded">{{ session('generated_password') }}</code>
            <br>
            <small class="text-muted">Comunica questa password all'utente. Non sarà più visibile dopo aver chiuso questo messaggio.</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
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
                        @if(!$user->isAdmin() || $user->id === Auth::id())
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Modifica Utente
                            </a>
                        @endif
                        
                        @if($user->account && $user->isClient())
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#depositModal">
                                <i class="fas fa-plus-circle me-2"></i>Deposita Fondi
                            </button>

                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                                <i class="fas fa-minus-circle me-2"></i>Preleva Fondi
                            </button>
                            
                            @if($user->account->is_active)
                                <a href="{{ route('admin.transactions.create-transfer-form', $user) }}" class="btn btn-primary">
                                    <i class="fas fa-money-bill-transfer me-2"></i>Crea Bonifico
                                </a>
                            @endif
                        @endif
                        
                        @if(!$user->isAdmin() && $user->id !== Auth::id())
                            @if($user->is_active)
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Confermi la disattivazione di questo utente?')">
                                        <i class="fas fa-user-slash me-2"></i>Disattiva Utente
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Confermi la riattivazione di questo utente?')">
                                        <i class="fas fa-user-check me-2"></i>Riattiva Utente
                                    </button>
                                </form>
                            @endif
                            
                            <button class="btn btn-danger" onclick="confirmDelete({{ $user->id }}, '{{ $user->full_name }}', '{{ $user->email }}', '{{ $user->username }}', '{{ ucfirst($user->role) }}', {{ $user->account ? $user->account->account_number : 'null' }}, {{ $user->account ? "'".$user->account->iban."'" : 'null' }}, {{ $user->account ? $user->account->balance : 0 }})">
                                <i class="fas fa-trash me-2"></i>Elimina Utente
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

            <!-- Protezioni Admin -->
            @if($user->isAdmin())
                <div class="card bg-transparent border-info mt-3">
                    <div class="card-header bg-info text-dark">
                        <h6><i class="fas fa-shield-alt me-2"></i>Account Amministratore</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><small>Gli account amministratore hanno protezioni speciali:</small></p>
                        <ul class="small mb-0">
                            <li>Non possono essere eliminati</li>
                            <li>Solo loro stessi possono modificare i propri dati</li>
                            <li>Non possono essere disattivati da altri admin</li>
                        </ul>
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
                        <div class="text-center mt-2">
                            <a href="{{ route('admin.transactions.index', ['client_id' => $user->id]) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-list me-1"></i>Vedi Tutte le Transazioni
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal Deposito -->
@if($user->account && $user->isClient())
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Deposita Fondi per {{ $user->full_name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.deposit', $user) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Importo (€) *</label>
                        <div class="input-group">
                            <span class="input-group-text">€</span>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" max="100000" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrizione *</label>
                        <input type="text" class="form-control" id="description" name="description" value="Deposito amministrativo" required maxlength="255">
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Il deposito verrà registrato immediatamente sul conto dell'utente.</small>
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
@endif

<!-- Modal Prelievo -->
@if($user->account && $user->isClient())
<div class="modal fade" id="withdrawalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Preleva Fondi per {{ $user->full_name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.withdrawal', $user) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="withdrawal_amount" class="form-label">Importo (€) *</label>
                        <div class="input-group">
                            <span class="input-group-text">€</span>
                            <input type="number" class="form-control" id="withdrawal_amount" name="amount" step="0.01" min="0.01" max="{{ $user->account->balance }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="withdrawal_description" class="form-label">Descrizione *</label>
                        <input type="text" class="form-control" id="withdrawal_description" name="description" value="Prelievo amministrativo" required maxlength="255">
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <small>Saldo disponibile: €{{ number_format($user->account->balance, 2, ',', '.') }}</small>
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
@endif

<!-- Modal Conferma Eliminazione -->
@if(!$user->isAdmin())
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Eliminazione Permanente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-skull-crossbones me-2"></i>
                    <strong>ATTENZIONE: ELIMINAZIONE PERMANENTE</strong>
                    <br>
                    <small>Questa operazione eliminerà completamente tutti i dati dal database.</small>
                </div>
                
                <p><strong>Utente da eliminare:</strong></p>
                <ul>
                    <li><strong>Nome:</strong> <span id="modal-user-name">{{ $user->full_name }}</span></li>
                    <li><strong>Email:</strong> <span id="modal-user-email">{{ $user->email }}</span></li>
                    <li><strong>Username:</strong> <span id="modal-user-username">{{ $user->username }}</span></li>
                    <li><strong>Ruolo:</strong> <span id="modal-user-role">{{ ucfirst($user->role) }}</span></li>
                </ul>
                
                @if($user->account)
                    <div class="alert alert-warning" id="modal-account-info">
                        <i class="fas fa-university me-2"></i>
                        <strong>Conto Corrente:</strong>
                        <br>
                        <small>Numero: <span id="modal-account-number">{{ $user->account->account_number }}</span></small>
                        <br>
                        <small>IBAN: <span id="modal-account-iban">{{ $user->account->iban }}</span></small>
                        @if($user->account->balance > 0)
                            <br>
                            <strong class="text-danger">Saldo: €<span id="modal-account-balance">{{ number_format($user->account->balance, 2, ',', '.') }}</span></strong>
                        @else
                            <br>
                            <small>Saldo: €<span id="modal-account-balance">0,00</span></small>
                        @endif
                    </div>
                @else
                    <div class="alert alert-warning" id="modal-account-info" style="display: none;">
                        <i class="fas fa-university me-2"></i>
                        <strong>Conto Corrente:</strong>
                        <br>
                        <small>Numero: <span id="modal-account-number"></span></small>
                        <br>
                        <small>IBAN: <span id="modal-account-iban"></span></small>
                        <br>
                        <strong class="text-danger">Saldo: €<span id="modal-account-balance"></span></strong>
                    </div>
                @endif
                
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>VERRÀ ELIMINATO DEFINITIVAMENTE:</strong>
                    <ul class="mb-0 mt-2">
                        <li>L'utente e tutti i suoi dati personali</li>
                        <li id="modal-account-deletion" {{ $user->account ? '' : 'style=display:none;' }}>Il conto corrente e tutte le transazioni</li>
                        <li id="modal-balance-deletion" {{ ($user->account && $user->account->balance > 0) ? '' : 'style=display:none;' }} class="text-danger"><strong>Il saldo di €<span id="modal-balance-amount">{{ $user->account ? number_format($user->account->balance, 2, ',', '.') : '0,00' }}</span></strong></li>
                        <li>Tutte le assegnazioni employee-client</li>
                        <li>I beneficiari salvati</li>
                        <li>Le domande di sicurezza</li>
                        <li><strong class="text-danger">TUTTO SARÀ IRRECUPERABILE</strong></li>
                    </ul>
                </div>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="confirmHardDelete" required>
                    <label class="form-check-label text-warning" for="confirmHardDelete">
                        <strong>Confermo di voler eliminare DEFINITIVAMENTE tutti i dati</strong>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annulla
                </button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                        <i class="fas fa-skull-crossbones me-1"></i>ELIMINA DEFINITIVAMENTE
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('confirmHardDelete');
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    
    if (checkbox && deleteBtn) {
        checkbox.addEventListener('change', function() {
            deleteBtn.disabled = !this.checked;
        });
    }
});

function confirmDelete(userId, userName, userEmail, userUsername, userRole, accountNumber, accountIban, accountBalance) {
    console.log('confirmDelete called with:', {userId, userName, userEmail, userUsername, userRole, accountNumber, accountIban, accountBalance});
    
    const form = document.getElementById('deleteForm');
    const checkbox = document.getElementById('confirmHardDelete');
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    
    // Imposta l'action del form
    form.action = `/admin/users/${userId}`;
    
    // Reset checkbox e button
    if (checkbox) checkbox.checked = false;
    if (deleteBtn) deleteBtn.disabled = true;
    
    // Popola i dati utente nel modal
    document.getElementById('modal-user-name').textContent = userName;
    document.getElementById('modal-user-email').textContent = userEmail;
    document.getElementById('modal-user-username').textContent = userUsername;
    document.getElementById('modal-user-role').textContent = userRole;
    
    // Gestisci le informazioni del conto
    const accountInfo = document.getElementById('modal-account-info');
    const accountDeletion = document.getElementById('modal-account-deletion');
    const balanceDeletion = document.getElementById('modal-balance-deletion');
    
    if (accountNumber && accountNumber !== 'null') {
        // L'utente ha un conto
        accountInfo.style.display = 'block';
        accountDeletion.style.display = 'list-item';
        
        document.getElementById('modal-account-number').textContent = accountNumber;
        document.getElementById('modal-account-iban').textContent = accountIban;
        document.getElementById('modal-account-balance').textContent = parseFloat(accountBalance).toFixed(2).replace('.', ',');
        
        if (accountBalance > 0) {
            balanceDeletion.style.display = 'list-item';
            document.getElementById('modal-balance-amount').textContent = parseFloat(accountBalance).toFixed(2).replace('.', ',');
        } else {
            balanceDeletion.style.display = 'none';
        }
    } else {
        // L'utente non ha un conto
        accountInfo.style.display = 'none';
        accountDeletion.style.display = 'none';
        balanceDeletion.style.display = 'none';
    }
    
    // Mostra il modal
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Auto-dismiss alerts after 10 seconds (password alerts stay longer)
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-warning)');
        alerts.forEach(function(alert) {
            if (bootstrap.Alert.getInstance(alert)) {
                bootstrap.Alert.getInstance(alert).close();
            }
        });
    }, 5000);
    
    // Password alerts dismiss after 15 seconds
    setTimeout(function() {
        const passwordAlerts = document.querySelectorAll('.alert-warning');
        passwordAlerts.forEach(function(alert) {
            if (bootstrap.Alert.getInstance(alert)) {
                bootstrap.Alert.getInstance(alert).close();
            }
        });
    }, 15000);
});
</script>
@endsection