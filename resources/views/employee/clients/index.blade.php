@extends('layouts.bootstrap')

@section('title', 'I Miei Clienti')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users me-2"></i>I Miei Clienti</h2>
                <div>
                    <a href="{{ route('employee.clients.create') }}" class="btn btn-success me-2">
                        <i class="fas fa-plus me-1"></i>Registra Cliente
                    </a>
                    <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i>Dashboard
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

    <!-- Filtri di Ricerca -->
    <div class="card bg-transparent border-light mb-4">
        <div class="card-header">
            <h6><i class="fas fa-search me-2"></i>Filtri di Ricerca</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('employee.clients.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Cerca Cliente</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Nome, email, username...">
                </div>
                <div class="col-md-4">
                    <label for="account_status" class="form-label">Stato Conto</label>
                    <select class="form-select" id="account_status" name="account_status">
                        <option value="">Tutti</option>
                        <option value="active" {{ request('account_status') === 'active' ? 'selected' : '' }}>Conto Attivo</option>
                        <option value="inactive" {{ request('account_status') === 'inactive' ? 'selected' : '' }}>Conto Bloccato</option>
                        <option value="no_account" {{ request('account_status') === 'no_account' ? 'selected' : '' }}>Senza Conto</option>
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

    <!-- Lista Clienti -->
    <div class="card bg-transparent border-light">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list me-2"></i>Clienti Assegnati ({{ $clients->total() }})</h5>
                <small class="text-muted">
                    Solo i clienti assegnati a te
                </small>
            </div>
        </div>
        <div class="card-body">
            @forelse($clients as $client)
                <div class="card bg-dark border-secondary mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $client->full_name }}</div>
                                        <small class="text-muted">{{ $client->username }}</small>
                                        <br>
                                        <small class="text-info">{{ $client->email }}</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                @if($client->account)
                                    <div>
                                        <small class="text-muted">Conto:</small><br>
                                        <span class="font-monospace small">{{ $client->account->account_number }}</span><br>
                                        <span class="text-success fw-bold">€{{ number_format($client->account->balance, 2, ',', '.') }}</span>
                                        @if(!$client->account->is_active)
                                            <br><span class="badge bg-danger">Bloccato</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge bg-warning">Nessun Conto</span>
                                @endif
                            </div>
                            
                            <div class="col-md-2">
                                <span class="badge {{ $client->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $client->is_active ? 'Attivo' : 'Inattivo' }}
                                </span>
                                <br>
                                <small class="text-muted">{{ $client->created_at->format('d/m/Y') }}</small>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('employee.clients.show', $client) }}" 
                                       class="btn btn-sm btn-outline-info" title="Visualizza">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('employee.clients.edit', $client) }}" 
                                       class="btn btn-sm btn-outline-warning" title="Modifica">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($client->account && $client->account->is_active)
                                        <button class="btn btn-sm btn-outline-success" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#depositModal{{ $client->id }}"
                                                title="Deposito">
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#transferModal{{ $client->id }}"
                                                title="Bonifico">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Deposito -->
                @if($client->account)
                <div class="modal fade" id="depositModal{{ $client->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content bg-dark">
                            <div class="modal-header">
                                <h5 class="modal-title">Deposito per {{ $client->full_name }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('employee.clients.deposit', $client) }}">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="amount{{ $client->id }}" class="form-label">Importo (€)</label>
                                        <input type="number" class="form-control" id="amount{{ $client->id }}" name="amount" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description{{ $client->id }}" class="form-label">Descrizione</label>
                                        <input type="text" class="form-control" id="description{{ $client->id }}" name="description" value="Deposito per {{ $client->full_name }}" required>
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
                <div class="modal fade" id="transferModal{{ $client->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content bg-dark">
                            <div class="modal-header">
                                <h5 class="modal-title">Bonifico per {{ $client->full_name }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('employee.clients.transfer', $client) }}">
                                @csrf
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="recipient_iban{{ $client->id }}" class="form-label">IBAN Beneficiario</label>
                                            <input type="text" class="form-control" id="recipient_iban{{ $client->id }}" name="recipient_iban" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="beneficiary_name{{ $client->id }}" class="form-label">Nome Beneficiario</label>
                                            <input type="text" class="form-control" id="beneficiary_name{{ $client->id }}" name="beneficiary_name">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="transfer_amount{{ $client->id }}" class="form-label">Importo (€)</label>
                                            <input type="number" class="form-control" id="transfer_amount{{ $client->id }}" name="amount" step="0.01" min="0.01" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="transfer_description{{ $client->id }}" class="form-label">Causale</label>
                                        <input type="text" class="form-control" id="transfer_description{{ $client->id }}" name="description" required>
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
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nessun cliente assegnato</h5>
                    <p class="text-muted">Non hai ancora clienti assegnati o non ci sono clienti che corrispondono ai filtri.</p>
                    <a href="{{ route('employee.clients.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Registra il primo cliente
                    </a>
                </div>
            @endforelse

            <!-- Paginazione -->
            @if($clients->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}
</style>

<script>
// Auto-clear modals on hide
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('hidden.bs.modal', function() {
        const form = this.querySelector('form');
        if (form) {
            form.reset();
        }
    });
});

// Formatta IBAN nei modal
document.querySelectorAll('[id^="recipient_iban"]').forEach(input => {
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '').toUpperCase();
        let formatted = value.replace(/(.{4})/g, '$1 ').trim();
        e.target.value = formatted;
    });
});
</script>
@endsection class="text-primary">{{ $user->assignedClients()->count() }}</h4>
                                        <p class="mb-0">Clienti Assegnati</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h4 class="text-success">
                                            {{ $user->assignedClients()->whereHas('account', function($q) { 
                                                $q->where('is_active', true); 
                                            })->count() }}
                                        </h4>
                                        <p class="mb-0">Conti Attivi</p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <h4 class="text-info">
                                            €{{ number_format($user->assignedClients()->whereHas('account')->get()->sum(function($client) { 
                                                return $client->account->balance ?? 0; 
                                            }), 2, ',', '.') }}
                                        </h4>
                                        <p class="mb-0">Saldo Gestito</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Azioni -->
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('client.profile.edit') }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Modifica Profilo
                        </a>
                        <a href="{{ route('client.profile.change-password') }}" class="btn btn-outline-warning">
                            <i class="fas fa-key me-2"></i>Cambia Password
                        </a>
                        @if($user->isClient())
                            <a href="{{ route('client.security.questions') }}" class="btn btn-outline-info">
                                <i class="fas fa-shield-alt me-2"></i>Sicurezza
                            </a>
                            <a href="{{ route('client.notifications.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-bell me-2"></i>Notifiche
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection