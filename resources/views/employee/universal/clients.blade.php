@extends('layouts.bootstrap')

@section('title', 'Depositi - Tutti i Clienti')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-coins me-2"></i>Operazioni - Tutti i Clienti</h2>
                <a href="{{ route('dashboard.employee') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard Employee
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistiche -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-info mb-2"></i>
                    <h4 class="text-info">{{ $stats['total_clients'] }}</h4>
                    <p class="mb-0">Clienti Totali</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                    <h4 class="text-success">{{ $stats['active_clients'] }}</h4>
                    <p class="mb-0">Clienti Attivi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-university fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning">{{ $stats['clients_with_account'] }}</h4>
                    <p class="mb-0">Con Conto</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-user-tie fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary">{{ $stats['assigned_to_me'] }}</h4>
                    <p class="mb-0">Assegnati a Te</p>
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
            <form method="GET" action="{{ route('employee.universal.clients') }}" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Cerca Cliente</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $search }}" placeholder="Nome, email, username...">
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Stato</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tutti</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Solo Attivi</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Solo Inattivi</option>
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
                <h5><i class="fas fa-list me-2"></i>Clienti ({{ $clients->total() }})</h5>
                <small class="text-muted">
                    Puoi effettuare depositi e prelievi per tutti i clienti
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
                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                        @if(Auth::user()->canManageClient($client))
                                            <i class="fas fa-user-tie text-white"></i>
                                        @else
                                            <i class="fas fa-user text-white"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $client->full_name }}</div>
                                        <small class="text-muted">{{ $client->username }}</small>
                                        @if(Auth::user()->canManageClient($client))
                                            <span class="badge bg-info ms-2">Assegnato</span>
                                        @endif
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
                                <small class="text-muted">Email:</small><br>
                                <small>{{ Str::limit($client->email, 20) }}</small><br>
                                <span class="badge {{ $client->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $client->is_active ? 'Attivo' : 'Inattivo' }}
                                </span>
                            </div>
                            
                            <div class="col-md-3">
                                @if($client->account && $client->account->is_active && $client->is_active)
                                    <div class="row">
                                        <div class="col-6">
                                            <button class="btn btn-info btn-sm w-100 mt-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#depositModal{{ $client->id }}">
                                                <i class="fas fa-cog me-1"></i>Deposito
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button class="btn btn-warning btn-sm w-100 mt-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#withdrawalModal{{ $client->id }}">
                                                <i class="fas fa-cog me-1"></i>Prelievo
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center">
                                        @if(!$client->account)
                                            <small class="text-warning">Nessun conto</small>
                                        @elseif(!$client->account->is_active)
                                            <small class="text-danger">Conto bloccato</small>
                                        @else
                                            <small class="text-danger">Cliente inattivo</small>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Deposito Dettagliato -->
                <div class="modal fade" id="depositModal{{ $client->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content bg-dark">
                            <div class="modal-header">
                                <h5 class="modal-title">Deposito per {{ $client->full_name }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('employee.universal.deposit', $client) }}">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Cliente:</label>
                                        <p class="form-control-plaintext">{{ $client->full_name }} ({{ $client->username }})</p>
                                    </div>
                                    
                                    @if($client->account)
                                        <div class="mb-3">
                                            <label class="form-label">Conto:</label>
                                            <p class="form-control-plaintext font-monospace">{{ $client->account->account_number }}</p>
                                            <p class="text-success">Saldo attuale: €{{ number_format($client->account->balance, 2, ',', '.') }}</p>
                                        </div>
                                    @endif
                                    
                                    <div class="mb-3">
                                        <label for="amount{{ $client->id }}" class="form-label">Importo (€) *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">€</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="amount{{ $client->id }}" 
                                                   name="amount" 
                                                   step="0.01" 
                                                   min="0.01" 
                                                   max="100000" 
                                                   required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description{{ $client->id }}" class="form-label">Descrizione *</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="description{{ $client->id }}" 
                                               name="description" 
                                               value="Deposito amministrativo" 
                                               maxlength="255" 
                                               required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-plus-circle me-1"></i>Effettua Deposito
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Modal Prelievo Dettagliato NUOVO -->
                <div class="modal fade" id="withdrawalModal{{ $client->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content bg-dark">
                            <div class="modal-header">
                                <h5 class="modal-title">Prelievo per {{ $client->full_name }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('employee.universal.withdrawal', $client) }}">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Cliente:</label>
                                        <p class="form-control-plaintext">{{ $client->full_name }} ({{ $client->username }})</p>
                                    </div>
                                    
                                    @if($client->account)
                                        <div class="mb-3">
                                            <label class="form-label">Conto:</label>
                                            <p class="form-control-plaintext font-monospace">{{ $client->account->account_number }}</p>
                                            <p class="text-warning">Saldo disponibile: €{{ number_format($client->account->balance, 2, ',', '.') }}</p>
                                        </div>
                                    @endif
                                    
                                    <div class="mb-3">
                                        <label for="withdrawal_amount{{ $client->id }}" class="form-label">Importo (€) *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">€</span>
                                            <input type="number" 
                                                class="form-control" 
                                                id="withdrawal_amount{{ $client->id }}" 
                                                name="amount" 
                                                step="0.01" 
                                                min="0.01" 
                                                max="{{ $client->account ? $client->account->balance : 0 }}" 
                                                required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="withdrawal_description{{ $client->id }}" class="form-label">Descrizione *</label>
                                        <input type="text" 
                                            class="form-control" 
                                            id="withdrawal_description{{ $client->id }}" 
                                            name="description" 
                                            value="Prelievo amministrativo" 
                                            maxlength="255" 
                                            required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-minus-circle me-1"></i>Effettua Prelievo
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>     
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nessun cliente trovato</h5>
                    <p class="text-muted">Non ci sono clienti che corrispondono ai filtri selezionati.</p>
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
</script>
@endsection