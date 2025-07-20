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
                    <a href="{{ route('dashboard.employee') }}" class="btn btn-outline-light">
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
                <div class="modal fade" id="transferModal{{ $client->id }}" tabindex="-1" data-client-iban="{{ $client->account->iban ?? '' }}">
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
                                            <label for="recipient_iban{{ $client->id }}" class="form-label">IBAN Beneficiario *</label>
                                            <input type="text" class="form-control" id="recipient_iban{{ $client->id }}" name="recipient_iban" 
                                                placeholder="IT60 X054 2811 1010 0000 0123 456" maxlength="34" required >
                                            <div class="form-text">
                                                Inserisci l'IBAN del beneficiario<br>
                                                <small id="modal-iban-length{{ $client->id }}" class="text-muted">Caratteri inseriti: 0</small>
                                            </div>
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
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('hidden.bs.modal', function() {
        const form = this.querySelector('form');
        if (form) {
            form.reset();
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Trova tutti i modal di trasferimento
    document.querySelectorAll('[id^="transferModal"]').forEach(function(modal) {
        const clientId = modal.id.replace('transferModal', '');
        const ibanInput = document.getElementById('recipient_iban' + clientId);
        const lengthCounter = document.getElementById('modal-iban-length' + clientId);
        const submitBtn = modal.querySelector('button[type="submit"]');
        
        if (ibanInput && lengthCounter && submitBtn) {
            // Formattazione e validazione IBAN - IDENTICA A "DETTAGLI CLIENTE"
            ibanInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s/g, '').toUpperCase();
                let formatted = value.replace(/(.{4})/g, '$1 ').trim();
                e.target.value = formatted;

                validateTransferForm(clientId);
            });
            
            // Validazione quando si apre il modal
            modal.addEventListener('shown.bs.modal', function() {
                submitBtn.disabled = true; // Inizialmente disabilitato
                validateTransferForm(clientId);
            });
        }
    });
});

// Funzione di validazione identica a "Dettagli Cliente"
function validateTransferForm(clientId) {
    const ibanInput = document.getElementById('recipient_iban' + clientId);
    const submitBtn = document.querySelector(`#transferModal${clientId} button[type="submit"]`);
    
    if (!ibanInput || !submitBtn) return;
    
    const iban = ibanInput.value.replace(/\s/g, '');
    
    // Ottieni l'IBAN del cliente dal data attribute del modal
    const modal = document.getElementById(`transferModal${clientId}`);
    const clientIban = modal ? modal.getAttribute('data-client-iban').replace(/\s/g, '') : '';
    const ibanEqual = iban === clientIban;
    
    // Controlla se esiste già un div errore
    let errorDiv = document.getElementById(`transfer-iban-error${clientId}`);
    
    // Controlla se esiste già un div per validazione IBAN
    let validationDiv = document.getElementById(`transfer-iban-validation${clientId}`);
    if (!validationDiv) {
        validationDiv = document.createElement('div');
        validationDiv.id = `transfer-iban-validation${clientId}`;
        validationDiv.className = 'form-text';
        ibanInput.parentNode.appendChild(validationDiv);
        
        // Nascondi l'elemento originale per evitare duplicati
        const originalCounter = document.getElementById(`modal-iban-length${clientId}`);
        if (originalCounter) {
            originalCounter.style.display = 'none';
        }
    }
    
    // Controllo formato IBAN (primi 4 caratteri)
    let hasFormatError = false;
    let formatErrorMessage = '';
    
    // Se ci sono almeno 2 caratteri, controlla che siano lettere
    if (iban.length >= 2) {
        const char1 = iban.charAt(0);
        const char2 = iban.charAt(1);
        
        if (!(char1 >= 'A' && char1 <= 'Z') || !(char2 >= 'A' && char2 <= 'Z')) {
            hasFormatError = true;
            formatErrorMessage = 'I primi due caratteri devono essere lettere (codice paese)';
        }
    }
    
    // Se ci sono almeno 4 caratteri e i primi 2 sono ok, controlla che il 3° e 4° siano cifre
    if (iban.length >= 4 && !hasFormatError) {
        const char3 = iban.charAt(2);
        const char4 = iban.charAt(3);
        
        if (!(char3 >= '0' && char3 <= '9') || !(char4 >= '0' && char4 <= '9')) {
            hasFormatError = true;
            formatErrorMessage = 'Il terzo e quarto carattere devono essere cifre (codice controllo)';
        }
    }
    
    // Validazione lunghezza e formato IBAN
    let isValidIban = false;
    
    if (iban.length === 0) {
        validationDiv.className = 'form-text text-muted';
        validationDiv.textContent = 'Caratteri inseriti: 0';
        submitBtn.disabled = true;
    } else if (hasFormatError) {
        validationDiv.className = 'form-text text-danger';
        validationDiv.textContent = `Caratteri inseriti: ${iban.length} ✗ ${formatErrorMessage}`;
        submitBtn.disabled = true;
        ibanInput.classList.add('is-invalid');
    } else if (iban.startsWith('IT') && iban.length === 27) {
        validationDiv.className = 'form-text text-success';
        validationDiv.textContent = `Caratteri inseriti: ${iban.length} ✓ IBAN italiano valido`;
        isValidIban = true;
    } else if (iban.startsWith('IT') && iban.length !== 27) {
        validationDiv.className = 'form-text text-danger';
        validationDiv.textContent = `Caratteri inseriti: ${iban.length} ✗ IBAN italiano deve avere 27 caratteri`;
    } else if (iban.length >= 15 && iban.length <= 34) {
        validationDiv.className = 'form-text text-warning';
        validationDiv.textContent = `Caratteri inseriti: ${iban.length} (verificare validità)`;
        isValidIban = true;
    } else if (iban.length > 0 && iban.length < 15) {
        validationDiv.className = 'form-text text-danger';
        validationDiv.textContent = `Caratteri inseriti: ${iban.length} ✗ Lunghezza non valida`;
    }
    
    // Controllo IBAN uguale al cliente
    if (ibanEqual && iban && clientIban) {
        // Crea div errore se non esiste
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = `transfer-iban-error${clientId}`;
            errorDiv.className = 'invalid-feedback d-block';
            ibanInput.parentNode.appendChild(errorDiv);
        }
        
        errorDiv.style.display = 'block';
        errorDiv.textContent = 'Non puoi inviare un bonifico al conto del cliente stesso';
        ibanInput.classList.add('is-invalid');
        submitBtn.disabled = true;
    } else if (hasFormatError || (!isValidIban && iban.length > 0)) {
        // Blocca se IBAN non valido o ha errori di formato
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = `transfer-iban-error${clientId}`;
            errorDiv.className = 'invalid-feedback d-block';
            ibanInput.parentNode.appendChild(errorDiv);
        }
        
        if (hasFormatError) {
            errorDiv.style.display = 'block';
            errorDiv.textContent = formatErrorMessage;
        } else {
            errorDiv.style.display = 'block';
            errorDiv.textContent = '';
        }
        ibanInput.classList.add('is-invalid');
        submitBtn.disabled = true;
    } else {
        // Rimuovi errore
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
        ibanInput.classList.remove('is-invalid');
        
        // Abilita pulsante solo se IBAN è valido e non vuoto
        if (isValidIban && iban.length > 0) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }
}
</script>
@endsection