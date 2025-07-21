@extends('layouts.bootstrap')

@section('title', 'Report Utenti')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users me-2"></i>Report Utenti</h2>
                <div>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i>Report Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtri Report -->
    <div class="card bg-transparent border-light mb-4">
        <div class="card-header">
            <h6><i class="fas fa-filter me-2"></i>Filtri Report</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.users') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="role" class="form-label">Ruolo</label>
                    <select class="form-select" id="role" name="role">
                        <option value="">Tutti i ruoli</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Amministratori</option>
                        <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Dipendenti</option>
                        <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Clienti</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Stato</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tutti gli stati</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Attivi</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inattivi</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="has_account" class="form-label">Conto Corrente</label>
                    <select class="form-select" id="has_account" name="has_account">
                        <option value="">Tutti</option>
                        <option value="yes" {{ request('has_account') === 'yes' ? 'selected' : '' }}>Con conto</option>
                        <option value="no" {{ request('has_account') === 'no' ? 'selected' : '' }}>Senza conto</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>Filtra
                        </button>
                        <a href="{{ route('admin.reports.users') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiche Generali -->
    @php
        $allUsers = \App\Models\User::all();
        $activeUsers = $allUsers->where('is_active', true);
        $inactiveUsers = $allUsers->where('is_active', false);
        $usersWithAccounts = $allUsers->filter(fn($user) => $user->account);
        $usersWithoutAccounts = $allUsers->filter(fn($user) => !$user->account);
        
        $stats = [
            'total_users' => $allUsers->count(),
            'active_users' => $activeUsers->count(),
            'inactive_users' => $inactiveUsers->count(),
            'admins' => $allUsers->where('role', 'admin')->count(),
            'employees' => $allUsers->where('role', 'employee')->count(),
            'clients' => $allUsers->where('role', 'client')->count(),
            'with_accounts' => $usersWithAccounts->count(),
            'without_accounts' => $usersWithoutAccounts->count(),
            'total_balance' => $usersWithAccounts->sum(fn($user) => $user->account ? $user->account->balance : 0),
        ];
    @endphp

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-transparent border-primary text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary">{{ number_format($stats['total_users']) }}</h4>
                    <p class="mb-0">Utenti Totali</p>
                    <small class="text-muted">{{ $stats['active_users'] }} attivi, {{ $stats['inactive_users'] }} inattivi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-success text-center">
                <div class="card-body">
                    <i class="fas fa-user fa-2x text-success mb-2"></i>
                    <h4 class="text-success">{{ number_format($stats['clients']) }}</h4>
                    <p class="mb-0">Clienti</p>
                    <small class="text-muted">{{ number_format(($stats['clients'] / $stats['total_users']) * 100, 1) }}% del totale</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-info text-center">
                <div class="card-body">
                    <i class="fas fa-university fa-2x text-info mb-2"></i>
                    <h4 class="text-info">{{ number_format($stats['with_accounts']) }}</h4>
                    <p class="mb-0">Con Conto</p>
                    <small class="text-muted">{{ $stats['without_accounts'] }} senza conto</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-warning text-center">
                <div class="card-body">
                    <i class="fas fa-euro-sign fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning">€{{ number_format($stats['total_balance'], 2, ',', '.') }}</h4>
                    <p class="mb-0">Saldo Totale</p>
                    <small class="text-muted">Tutti i conti</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafici -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h6><i class="fas fa-chart-pie me-2"></i>Distribuzione per Ruolo</h6>
                </div>
                <div class="card-body">
                    <canvas id="roleChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h6><i class="fas fa-chart-doughnut me-2"></i>Stato Utenti</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h6><i class="fas fa-chart-bar me-2"></i>Registrazioni per Mese</h6>
                </div>
                <div class="card-body">
                    <canvas id="registrationChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabella Utenti -->
    <div class="card bg-transparent border-light">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-table me-2"></i>Lista Utenti</h5>
                <div class="text-muted">
                    @if(request()->hasAny(['role', 'status', 'has_account']))
                        <span class="badge bg-info me-2">Filtrato</span>
                    @endif
                    {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} di {{ $users->total() }}
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-dark table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Utente</th>
                                <th>Email</th>
                                <th>Ruolo</th>
                                <th>Stato</th>
                                <th>Conto</th>
                                <th class="text-end">Saldo</th>
                                <th>Registrato</th>
                                <th class="text-center">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <span class="font-monospace">#{{ $user->id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'employee' ? 'warning' : 'success') }} rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $user->full_name }}</div>
                                            <small class="text-muted">{{ $user->username }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ $user->email }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'employee' ? 'warning' : 'success') }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->is_active ? 'Attivo' : 'Inattivo' }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->account)
                                        <div>
                                            <span class="badge {{ $user->account->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $user->account->is_active ? 'Attivo' : 'Bloccato' }}
                                            </span>
                                            <br>
                                            <small class="text-info">{{ $user->account->account_number }}</small>
                                        </div>
                                    @else
                                        @if($user->role === 'client')
                                            <span class="badge bg-warning">Nessun Conto</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($user->account)
                                        <span class="fw-bold text-{{ $user->account->balance >= 0 ? 'success' : 'danger' }}">
                                            €{{ number_format($user->account->balance, 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $user->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="Visualizza">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($user->account)
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-{{ $user->account->is_active ? 'danger' : 'success' }}" 
                                                    onclick="toggleAccount({{ $user->id }}, '{{ $user->account->is_active ? 'bloccare' : 'sbloccare' }}', '{{ $user->full_name }}')"
                                                    title="{{ $user->account->is_active ? 'Blocca' : 'Sblocca' }} Conto">
                                                <i class="fas fa-{{ $user->account->is_active ? 'lock' : 'unlock' }}"></i>
                                            </button>
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
                        Visualizzati {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} 
                        di {{ $users->total() }} utenti
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($users->hasPages())
                            <small class="text-muted me-2">Pagina:</small>
                            <nav aria-label="Paginazione utenti">
                                {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
                            </nav>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nessun utente trovato</h5>
                    <p class="text-muted">Non ci sono utenti che corrispondono ai filtri selezionati.</p>
                    @if(request()->hasAny(['role', 'status', 'has_account']))
                        <a href="{{ route('admin.reports.users') }}" class="btn btn-primary">
                            <i class="fas fa-eye me-1"></i>Visualizza tutti gli utenti
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Riepilogo Dettagliato -->
    @if($users->count() > 0)
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card bg-transparent border-secondary">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-line me-2"></i>Statistiche Avanzate</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <p><strong>Utenti Attivi:</strong></p>
                                <p><strong>Utenti con Email Verificata:</strong></p>
                                <p><strong>Clienti con Conto:</strong></p>
                                <p><strong>Saldo Medio per Cliente:</strong></p>
                            </div>
                            <div class="col-6 text-end">
                                <p>{{ number_format(($stats['active_users'] / $stats['total_users']) * 100, 1) }}%</p>
                                <p>{{ number_format(($allUsers->whereNotNull('email_verified_at')->count() / $stats['total_users']) * 100, 1) }}%</p>
                                <p>{{ number_format(($allUsers->where('role', 'client')->filter(fn($u) => $u->account)->count() / $stats['clients']) * 100, 1) }}%</p>
                                <p>€{{ number_format($stats['clients'] > 0 ? $stats['total_balance'] / $stats['clients'] : 0, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-transparent border-info">
                    <div class="card-header">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Anomalie e Alert</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $clientsWithoutAccount = $allUsers->where('role', 'client')->filter(fn($u) => !$u->account)->count();
                            $inactiveUsers = $allUsers->where('is_active', false)->count();
                            $unverifiedEmails = $allUsers->whereNull('email_verified_at')->count();
                            $negativeBalances = $allUsers->filter(fn($u) => $u->account && $u->account->balance < 0)->count();
                        @endphp
                        
                        <div class="alert-list">
                            @if($clientsWithoutAccount > 0)
                                <div class="alert alert-warning py-2 mb-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>{{ $clientsWithoutAccount }}</strong> clienti senza conto corrente
                                </div>
                            @endif
                            
                            @if($inactiveUsers > 0)
                                <div class="alert alert-danger py-2 mb-2">
                                    <i class="fas fa-user-slash me-2"></i>
                                    <strong>{{ $inactiveUsers }}</strong> utenti disattivati
                                </div>
                            @endif
                            
                            @if($negativeBalances > 0)
                                <div class="alert alert-warning py-2 mb-2">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    <strong>{{ $negativeBalances }}</strong> conti con saldo negativo
                                </div>
                            @endif
                            
                            @if($clientsWithoutAccount == 0 && $inactiveUsers == 0 && $unverifiedEmails == 0 && $negativeBalances == 0)
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Nessuna anomalia rilevata
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal Toggle Account -->
<div class="modal fade" id="toggleAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="toggleAccountTitle">Conferma Operazione</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="toggleAccountMessage"></p>
                <p class="text-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Questa operazione avrà effetto immediato.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <form id="toggleAccountForm" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn" id="toggleAccountBtn">Conferma</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dati per i grafici
    const roleData = {
        admin: {{ $stats['admins'] }},
        employee: {{ $stats['employees'] }},
        client: {{ $stats['clients'] }}
    };

    const statusData = {
        active: {{ $stats['active_users'] }},
        inactive: {{ $stats['inactive_users'] }}
    };

    // Dati registrazioni per mese
    @php
        $registrationData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = \App\Models\User::whereYear('created_at', $date->year)
                                    ->whereMonth('created_at', $date->month)
                                    ->count();
            $registrationData[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }
    @endphp

    const registrationData = @json($registrationData);

    // Grafico Ruoli
    const roleCtx = document.getElementById('roleChart').getContext('2d');
    new Chart(roleCtx, {
        type: 'pie',
        data: {
            labels: ['Amministratori', 'Dipendenti', 'Clienti'],
            datasets: [{
                data: [roleData.admin, roleData.employee, roleData.client],
                backgroundColor: ['#dc3545', '#ffc107', '#28a745'],
                borderColor: ['#c82333', '#e0a800', '#1e7e34'],
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
                        padding: 15
                    }
                }
            }
        }
    });

    // Grafico Stati
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Attivi', 'Inattivi'],
            datasets: [{
                data: [statusData.active, statusData.inactive],
                backgroundColor: ['#28a745', '#dc3545'],
                borderColor: ['#1e7e34', '#c82333'],
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
                        padding: 15
                    }
                }
            }
        }
    });

    // Grafico Registrazioni
    const registrationCtx = document.getElementById('registrationChart').getContext('2d');
    new Chart(registrationCtx, {
        type: 'line',
        data: {
            labels: registrationData.map(item => item.month),
            datasets: [{
                label: 'Nuove Registrazioni',
                data: registrationData.map(item => item.count),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
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
                        color: 'white',
                        stepSize: 1
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
});

function toggleAccount(userId, action, userName) {
    const modal = document.getElementById('toggleAccountModal');
    const title = document.getElementById('toggleAccountTitle');
    const message = document.getElementById('toggleAccountMessage');
    const form = document.getElementById('toggleAccountForm');
    const btn = document.getElementById('toggleAccountBtn');
    
    title.textContent = `Conferma ${action.charAt(0).toUpperCase() + action.slice(1)} Conto`;
    message.innerHTML = `Sei sicuro di voler <strong>${action}</strong> il conto di <strong>${userName}</strong>?`;
    
    form.action = `/admin/users/${userId}/toggle-account`;
    
    if (action === 'bloccare') {
        btn.className = 'btn btn-danger';
        btn.innerHTML = '<i class="fas fa-lock me-1"></i>Blocca Conto';
    } else {
        btn.className = 'btn btn-success';
        btn.innerHTML = '<i class="fas fa-unlock me-1"></i>Sblocca Conto';
    }
    
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
}

function exportUsersData() {
    // Raccoglie i filtri attuali
    const params = new URLSearchParams();
    const role = document.getElementById('role').value;
    const status = document.getElementById('status').value;
    const hasAccount = document.getElementById('has_account').value;
    
    if (role) params.append('role', role);
    if (status) params.append('status', status);
    if (hasAccount) params.append('has_account', hasAccount);
    params.append('export', '1');
}
</script>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}

.alert-list .alert {
    border-left: 4px solid;
}

.alert-warning {
    border-left-color: #ffc107 !important;
}

.alert-danger {
    border-left-color: #dc3545 !important;
}

.alert-info {
    border-left-color: #17a2b8 !important;
}

.alert-success {
    border-left-color: #28a745 !important;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        border-radius: 0.375rem !important;
        margin-bottom: 0.25rem;
    }
}
</style>
@endsection