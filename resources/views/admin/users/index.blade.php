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
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="btn btn-sm btn-outline-warning" 
                                           title="Modifica">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(!$user->isAdmin())
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete({{ $user->id }})"
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
                <div class="d-flex justify-content-center mt-3">
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nessun utente trovato</h5>
                    <p class="text-muted">Non ci sono utenti che corrispondono ai filtri selezionati.</p>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Crea il primo utente
                    </a>
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
                <h5 class="modal-title">Conferma Eliminazione</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Sei sicuro di voler eliminare questo utente?</p>
                <p class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Questa azione disattiverà l'utente e il suo conto.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Elimina</button>
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

// Stile per avatar
.avatar-sm {
    width: 32px;
    height: 32px;
}

.btn-xs {
    padding: 0.125rem 0.25rem;
    font-size: 0.75rem;
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