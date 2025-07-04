@extends('layouts.bootstrap')

@section('title', 'Gestione Utenti')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users me-2"></i>Gestione Utenti</h2>
                <div>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success me-2">
                        <i class="fas fa-plus me-1"></i>Nuovo Utente
                    </a>
                    <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtri di Ricerca -->
    <div class="card bg-transparent border-light mb-4">
        <div class="card-header">
            <h6><i class="fas fa-search me-2"></i>Filtri di Ricerca</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Cerca</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $search }}" placeholder="Nome, email, username...">
                </div>
                <div class="col-md-3">
                    <label for="role" class="form-label">Ruolo</label>
                    <select class="form-select" id="role" name="role">
                        <option value="">Tutti</option>
                        <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Amministratori</option>
                        <option value="employee" {{ $role === 'employee' ? 'selected' : '' }}>Dipendenti</option>
                        <option value="client" {{ $role === 'client' ? 'selected' : '' }}>Clienti</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Stato</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tutti</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Attivi</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inattivi</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Cerca
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista Utenti -->
    <div class="card bg-transparent border-light">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list me-2"></i>Utenti ({{ $users->total() }})</h5>
                <div class="text-muted">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-dark table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Utente</th>
                                <th>Email</th>
                                <th>Ruolo</th>
                                <th>Conto</th>
                                <th>Stato</th>
                                <th>Registrato</th>
                                <th class="text-center">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $user->full_name }}</div>
                                            <small class="text-muted">{{ $user->username }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @switch($user->role)
                                        @case('admin')
                                            <span class="badge bg-danger">Amministratore</span>
                                            @break
                                        @case('employee')
                                            <span class="badge bg-warning">Dipendente</span>
                                            @break
                                        @case('client')
                                            <span class="badge bg-success">Cliente</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($user->role) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($user->account)
                                        <div>
                                            <span class="badge {{ $user->account->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $user->account->is_active ? 'Attivo' : 'Bloccato' }}
                                            </span>
                                            <br>
                                            <small class="text-info">{{ $user->account->account_number }}</small>
                                            <br>
                                            <small class="text-success">€{{ number_format($user->account->balance, 2, ',', '.') }}</small>
                                        </div>
                                    @else
                                        @if($user->role === 'client')
                                            <span class="badge bg-warning">Nessun Conto</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->is_active ? 'Attivo' : 'Sospeso' }}
                                    </span>
                                </td>
                                <td>
                                    {{ $user->created_at->format('d/m/Y') }}
                                    <br>
                                    <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                        class="btn btn-sm btn-outline-info" 
                                        title="Visualizza">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!$user->isAdmin())
                                            <a href="{{ route('admin.users.edit', $user) }}" 
                                            class="btn btn-sm btn-outline-warning" 
                                            title="Modifica">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(!$user->isAdmin())
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete({{ $user->id }}, '{{ $user->full_name }}', '{{ $user->email }}', '{{ $user->username }}', '{{ ucfirst($user->role) }}', {{ $user->account ? $user->account->account_number : 'null' }}, {{ $user->account ? "'".$user->account->iban."'" : 'null' }}, {{ $user->account ? $user->account->balance : 0 }})"
                                                    title="Elimina">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <!-- Azioni aggiuntive per clienti -->
                                    @if($user->role === 'client')
                                        <div class="mt-1">
                                            @if(!$user->account)
                                                <form method="POST" action="{{ route('admin.users.create-account', $user) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-success" title="Crea Conto">
                                                        <i class="fas fa-plus"></i> Conto
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.users.toggle-account', $user) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-xs {{ $user->account->is_active ? 'btn-warning' : 'btn-success' }}" 
                                                            title="{{ $user->account->is_active ? 'Blocca Conto' : 'Sblocca Conto' }}">
                                                        <i class="fas {{ $user->account->is_active ? 'fa-lock' : 'fa-unlock' }}"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
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
                        Visualizzati {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} 
                        di {{ $users->total() }} utenti
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($users->hasPages())
                            <small class="text-muted me-2">Pagina:</small>
                            <nav aria-label="Paginazione utenti">
                                {{ $users->links('pagination::bootstrap-4') }}
                            </nav>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nessun utente trovato</h5>
                    <p class="text-muted">Non ci sono utenti che corrispondono ai filtri selezionati.</p>
                    @if(request()->hasAny(['search', 'role', 'status']))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                            <i class="fas fa-eye me-1"></i>Visualizza tutti gli utenti
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal di conferma eliminazione -->
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
                    <li><strong>Nome:</strong> <span id="modal-user-name"></span></li>
                    <li><strong>Email:</strong> <span id="modal-user-email"></span></li>
                    <li><strong>Username:</strong> <span id="modal-user-username"></span></li>
                    <li><strong>Ruolo:</strong> <span id="modal-user-role"></span></li>
                </ul>
                
                <div id="modal-account-info" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="fas fa-university me-2"></i>
                        <strong>Conto Corrente:</strong>
                        <br>
                        <small>Numero: <span id="modal-account-number"></span></small>
                        <br>
                        <small>IBAN: <span id="modal-account-iban"></span></small>
                        <br>
                        <strong class="text-danger">Saldo: €<span id="modal-account-balance"></span></strong>
                    </div>
                </div>
                
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>VERRÀ ELIMINATO DEFINITIVAMENTE:</strong>
                    <ul class="mb-0 mt-2">
                        <li>L'utente e tutti i suoi dati personali</li>
                        <li id="modal-account-deletion" style="display: none;">Il conto corrente e tutte le transazioni</li>
                        <li id="modal-balance-deletion" style="display: none;" class="text-danger"><strong>Il saldo di €<span id="modal-balance-amount"></span></strong></li>
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
</script>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}

.btn-xs {
    padding: 0.125rem 0.25rem;
    font-size: 0.75rem;
    line-height: 1.2;
}
</style>
@endsection