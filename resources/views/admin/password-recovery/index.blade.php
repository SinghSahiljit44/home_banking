@extends('layouts.bootstrap')

@section('title', 'Recupero Credenziali')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-key me-2"></i>Recupero Credenziali</h2>
                <div>
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.password-recovery.audit-log') }}" class="btn btn-outline-info me-2">
                            <i class="fas fa-history me-1"></i>Audit Log
                        </a>
                    @endif
                    <a href="{{ Auth::user()->isAdmin() ? route('dashboard.admin') : route('dashboard.employee') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            @if(session('new_password'))
                <br><strong>Nuova password:</strong> <code>{{ session('new_password') }}</code>
                <br><strong>Utente:</strong> {{ session('target_user') }}
            @endif
            @if(session('bulk_results'))
                <hr>
                <strong>Risultati operazione multipla:</strong>
                @foreach(session('bulk_results') as $result)
                    <br>• {{ $result['user']->full_name }}: <code>{{ $result['password'] }}</code>
                @endforeach
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
            @if(session('bulk_errors'))
                <hr>
                <strong>Errori operazione multipla:</strong>
                @foreach(session('bulk_errors') as $error)
                    <br>• {{ $error }}
                @endforeach
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistiche -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary">{{ $stats['total_available'] }}</h4>
                    <p class="mb-0">Utenti Gestibili</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-user fa-2x text-success mb-2"></i>
                    <h4 class="text-success">{{ $stats['clients_count'] }}</h4>
                    <p class="mb-0">Clienti</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-user-tie fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning">{{ $stats['employees_count'] }}</h4>
                    <p class="mb-0">Dipendenti</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtri di Ricerca -->
    <div class="card bg-transparent border-light mb-4">
        <div class="card-header">
            <h6><i class="fas fa-search me-2"></i>Cerca Utente</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route(Auth::user()->isAdmin() ? 'admin.password-recovery.index' : 'employee.password-recovery.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Cerca</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Nome, email, username...">
                </div>
                @if(Auth::user()->isAdmin())
                    <div class="col-md-4">
                        <label for="role" class="form-label">Ruolo</label>
                        <select class="form-select" id="role" name="role">
                            <option value="">Tutti</option>
                            <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Clienti</option>
                            <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Dipendenti</option>
                        </select>
                    </div>
                @endif
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

    <!-- Reset Password Singolo -->
    <div class="card bg-transparent border-light mb-4">
        <div class="card-header">
            <h5><i class="fas fa-key me-2"></i>Reset Password</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route(Auth::user()->isAdmin() ? 'admin.password-recovery.generate' : 'employee.password-recovery.generate') }}" id="passwordResetForm">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="user_id" class="form-label">Seleziona Utente *</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            <option value="">Seleziona utente</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->full_name }} ({{ $user->username }})
                                    @if(Auth::user()->isAdmin())
                                        - {{ ucfirst($user->role) }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="password_length" class="form-label">Lunghezza</label>
                        <select class="form-select" id="password_length" name="password_length">
                            <option value="8">8 caratteri</option>
                            <option value="10">10 caratteri</option>
                            <option value="12" selected>12 caratteri</option>
                            <option value="16">16 caratteri</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="reason" class="form-label">Motivo *</label>
                        <input type="text" class="form-control @error('reason') is-invalid @enderror" 
                               id="reason" name="reason" value="{{ old('reason') }}" 
                               placeholder="Motivo del reset" maxlength="500" required>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-refresh me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="notify_user" name="notify_user" value="1">
                    <label class="form-check-label" for="notify_user">
                        Invia notifica email all'utente
                    </label>
                </div>
            </form>
        </div>
    </div>

    @if(Auth::user()->isAdmin())
        <!-- Reset Username (Solo Admin) -->
        <div class="card bg-transparent border-secondary mb-4">
            <div class="card-header">
                <h5><i class="fas fa-user-edit me-2"></i>Reset Username (Solo Admin)</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.password-recovery.reset-username') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="username_user_id" class="form-label">Seleziona Utente *</label>
                            <select class="form-select" id="username_user_id" name="user_id" required>
                                <option value="">Seleziona utente</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->full_name }} ({{ $user->username }}) - {{ ucfirst($user->role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="new_username" class="form-label">Nuovo Username *</label>
                            <input type="text" class="form-control" id="new_username" name="new_username" 
                                   placeholder="nuovo.username" maxlength="50" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="username_reason" class="form-label">Motivo *</label>
                            <input type="text" class="form-control" id="username_reason" name="reason" 
                                   placeholder="Motivo del cambio" maxlength="500" required>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-edit me-1"></i>Cambia
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reset Multiplo (Solo Admin) -->
        <div class="card bg-transparent border-warning mb-4">
            <div class="card-header">
                <button class="btn btn-link text-warning p-0" type="button" data-bs-toggle="collapse" data-bs-target="#bulkReset">
                    <h5><i class="fas fa-users me-2"></i>Reset Multiplo (Solo Admin) <i class="fas fa-chevron-down"></i></h5>
                </button>
            </div>
            <div class="collapse" id="bulkReset">
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attenzione:</strong> Questa operazione resetterà le password di tutti gli utenti selezionati.
                    </div>
                    
                    <form method="POST" action="{{ route('admin.password-recovery.bulk-reset') }}" id="bulkResetForm">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="bulk_password_length" class="form-label">Lunghezza Password</label>
                                <select class="form-select" id="bulk_password_length" name="password_length">
                                    <option value="8">8 caratteri</option>
                                    <option value="10">10 caratteri</option>
                                    <option value="12" selected>12 caratteri</option>
                                    <option value="16">16 caratteri</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="bulk_reason" class="form-label">Motivo *</label>
                                <input type="text" class="form-control" id="bulk_reason" name="reason" 
                                       placeholder="Motivo del reset multiplo" maxlength="500" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="bulk_notify_users" name="notify_users" value="1">
                                    <label class="form-check-label" for="bulk_notify_users">
                                        Notifica via email
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Seleziona Utenti *</label>
                            <div class="row">
                                @foreach($users->chunk(3) as $userChunk)
                                    @foreach($userChunk as $user)
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="user_ids[]" 
                                                       value="{{ $user->id }}" id="bulk_user_{{ $user->id }}">
                                                <label class="form-check-label" for="bulk_user_{{ $user->id }}">
                                                    {{ $user->full_name }}
                                                    <small class="text-muted">({{ ucfirst($user->role) }})</small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                            <div class="form-text">
                                <button type="button" class="btn btn-sm btn-outline-light me-2" onclick="selectAllUsers()">
                                    Seleziona Tutti
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-light" onclick="deselectAllUsers()">
                                    Deseleziona Tutti
                                </button>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-warning btn-lg" onclick="return confirmBulkReset()">
                                <i class="fas fa-refresh me-2"></i>Esegui Reset Multiplo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Lista Utenti Disponibili -->
    <div class="card bg-transparent border-light">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list me-2"></i>Utenti Disponibili ({{ $users->count() }})</h5>
                <small class="text-muted">
                    {{ Auth::user()->isAdmin() ? 'Tutti gli utenti' : 'Solo clienti assegnati' }}
                </small>
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
                                @if(Auth::user()->isAdmin())
                                    <th>Ruolo</th>
                                @endif
                                <th>Stato</th>
                                <th>Ultimo Accesso</th>
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
                                @if(Auth::user()->isAdmin())
                                    <td>
                                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'employee' ? 'warning' : 'success') }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                @endif
                                <td>
                                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->is_active ? 'Attivo' : 'Sospeso' }}
                                    </span>
                                </td>
                                <td>
                                    {{ $user->updated_at->format('d/m/Y H:i') }}
                                    <br>
                                    <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-warning" 
                                                onclick="quickResetPassword({{ $user->id }}, '{{ $user->full_name }}')"
                                                title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>

                                        @if(Auth::user()->isAdmin())
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info" 
                                                    onclick="quickResetUsername({{ $user->id }}, '{{ $user->full_name }}', '{{ $user->username }}')"
                                                    title="Reset Username">
                                                <i class="fas fa-user-edit"></i>
                                            </button>
                                        @endif

                                        @if(!$user->is_active)
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success" 
                                                    onclick="unlockAccount({{ $user->id }}, '{{ $user->full_name }}')"
                                                    title="Sblocca Account">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nessun utente trovato</h5>
                    <p class="text-muted">
                        {{ Auth::user()->isAdmin() ? 'Non ci sono utenti disponibili.' : 'Non hai clienti assegnati.' }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Reset Password Rapido -->
<div class="modal fade" id="quickResetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password Rapido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route(Auth::user()->isAdmin() ? 'admin.password-recovery.generate' : 'employee.password-recovery.generate') }}" id="quickResetForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="quick_user_id" name="user_id">
                    <p><strong>Utente:</strong> <span id="quick_user_name"></span></p>
                    
                    <div class="mb-3">
                        <label for="quick_password_length" class="form-label">Lunghezza Password</label>
                        <select class="form-select" id="quick_password_length" name="password_length">
                            <option value="8">8 caratteri</option>
                            <option value="10">10 caratteri</option>
                            <option value="12" selected>12 caratteri</option>
                            <option value="16">16 caratteri</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quick_reason" class="form-label">Motivo *</label>
                        <input type="text" class="form-control" id="quick_reason" name="reason" 
                               placeholder="Motivo del reset" maxlength="500" required>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="quick_notify_user" name="notify_user" value="1">
                        <label class="form-check-label" for="quick_notify_user">
                            Invia notifica email all'utente
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key me-1"></i>Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(Auth::user()->isAdmin())
    <!-- Modal Reset Username Rapido -->
    <div class="modal fade" id="quickUsernameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Username Rapido</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.password-recovery.reset-username') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="quick_username_user_id" name="user_id">
                        <p><strong>Utente:</strong> <span id="quick_username_user_name"></span></p>
                        <p><strong>Username Attuale:</strong> <span id="quick_current_username" class="font-monospace"></span></p>
                        
                        <div class="mb-3">
                            <label for="quick_new_username" class="form-label">Nuovo Username *</label>
                            <input type="text" class="form-control" id="quick_new_username" name="new_username" 
                                   placeholder="nuovo.username" maxlength="50" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quick_username_reason" class="form-label">Motivo *</label>
                            <input type="text" class="form-control" id="quick_username_reason" name="reason" 
                                   placeholder="Motivo del cambio" maxlength="500" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-user-edit me-1"></i>Cambia Username
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sblocca Account -->
    <div class="modal fade" id="unlockAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Sblocca Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.password-recovery.unlock-account') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="unlock_user_id" name="user_id">
                        <p><strong>Utente:</strong> <span id="unlock_user_name"></span></p>
                        <p class="text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            L'account verrà riattivato e l'utente potrà accedere nuovamente.
                        </p>
                        
                        <div class="mb-3">
                            <label for="unlock_reason" class="form-label">Motivo *</label>
                            <input type="text" class="form-control" id="unlock_reason" name="reason" 
                                   placeholder="Motivo dello sblocco" maxlength="500" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-unlock me-1"></i>Sblocca Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<script>
function quickResetPassword(userId, userName) {
    document.getElementById('quick_user_id').value = userId;
    document.getElementById('quick_user_name').textContent = userName;
    
    const modal = new bootstrap.Modal(document.getElementById('quickResetModal'));
    modal.show();
}

@if(Auth::user()->isAdmin())
    function quickResetUsername(userId, userName, currentUsername) {
        document.getElementById('quick_username_user_id').value = userId;
        document.getElementById('quick_username_user_name').textContent = userName;
        document.getElementById('quick_current_username').textContent = currentUsername;
        
        const modal = new bootstrap.Modal(document.getElementById('quickUsernameModal'));
        modal.show();
    }

    function unlockAccount(userId, userName) {
        document.getElementById('unlock_user_id').value = userId;
        document.getElementById('unlock_user_name').textContent = userName;
        
        const modal = new bootstrap.Modal(document.getElementById('unlockAccountModal'));
        modal.show();
    }

    function selectAllUsers() {
        const checkboxes = document.querySelectorAll('input[name="user_ids[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = true);
    }

    function deselectAllUsers() {
        const checkboxes = document.querySelectorAll('input[name="user_ids[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = false);
    }

    function confirmBulkReset() {
        const checkedBoxes = document.querySelectorAll('input[name="user_ids[]"]:checked');
        if (checkedBoxes.length === 0) {
            alert('Seleziona almeno un utente.');
            return false;
        }
        
        return confirm(`Confermi il reset delle password per ${checkedBoxes.length} utenti selezionati?`);
    }
@endif

// Auto-dismiss alerts after 10 seconds (longer for password results)
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 10000);
});
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

code {
    background-color: rgba(255, 255, 255, 0.1);
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-family: 'Courier New', monospace;
}
</style>
@endsection