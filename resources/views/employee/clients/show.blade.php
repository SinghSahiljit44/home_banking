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
                            
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                                <i class="fas fa-minus-circle me-2"></i>Preleva Fondi
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
<!-- Modal Deposito con Validazione -->
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
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" max="49999.99" required>
                        <div id="deposit-amount-feedback" class="form-text text-muted">
                            Inserisci un importo tra €0.01 e €49,999.99
                        </div>
                        <div id="deposit-amount-error" class="invalid-feedback" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Non è possibile effettuare depositi di importo uguale o superiore a €50000
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrizione</label>
                        <input type="text" class="form-control" id="description" name="description" value="Deposito per {{ $client->full_name }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success" id="deposit-submit-btn">Deposita</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="withdrawalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Preleva Fondi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('employee.clients.withdrawal', $client) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="withdrawal_amount" class="form-label">Importo (€)</label>
                        <input type="number" class="form-control" id="withdrawal_amount" name="amount" step="0.01" min="0.01" max="{{ $client->account->balance }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="withdrawal_description" class="form-label">Descrizione</label>
                        <input type="text" class="form-control" id="withdrawal_description" name="description" value="Prelievo per {{ $client->full_name }}" required>
                    </div>
                    <div class="alert alert-warning">
                        <small>Saldo disponibile: €{{ number_format($client->account->balance, 2, ',', '.') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-danger">Preleva</button>
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

// Formatta IBAN nel modal bonifico con validazione completa
document.getElementById('recipient_iban')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '').toUpperCase();
    let formatted = value.replace(/(.{4})/g, '$1 ').trim();
    e.target.value = formatted;

    validateTransferForm();
});

function validateTransferForm() {
    const ibanInput = document.getElementById('recipient_iban');
    const submitBtn = document.querySelector('#transferModal button[type="submit"]');
    
    if (!ibanInput || !submitBtn) return;
    
    const iban = ibanInput.value.replace(/\s/g, '');
    const clientIban = '{{ $client->account->iban ?? "" }}'.replace(/\s/g, '');
    const ibanEqual = iban === clientIban;
    
    // Controlla se esiste già un div errore
    let errorDiv = document.getElementById('transfer-iban-error');
    
    // Controlla se esiste già un div per validazione IBAN
    let validationDiv = document.getElementById('transfer-iban-validation');
    if (!validationDiv) {
        validationDiv = document.createElement('div');
        validationDiv.id = 'transfer-iban-validation';
        validationDiv.className = 'form-text';
        ibanInput.parentNode.appendChild(validationDiv);
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
    if (ibanEqual && iban) {
        // Crea div errore se non esiste
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = 'transfer-iban-error';
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
            errorDiv.id = 'transfer-iban-error';
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

// Inizializza validazione quando si apre il modal
document.getElementById('transferModal')?.addEventListener('shown.bs.modal', function() {
    const submitBtn = document.querySelector('#transferModal button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true; // Inizialmente disabilitato
    }
    validateTransferForm();
});

function validateDepositAmount() {
    const amountInput = document.getElementById('amount');
    const submitBtn = document.getElementById('deposit-submit-btn');
    const errorDiv = document.getElementById('deposit-amount-error');
    const feedbackDiv = document.getElementById('deposit-amount-feedback');
    
    if (!amountInput || !submitBtn) return;
    
    const amount = parseFloat(amountInput.value);
    
    // Controlla PRIMA la validità nativa del campo (mantiene gli hint del browser)
    const isNativelyValid = amountInput.validity.valid;
    const nativeValidationMessage = amountInput.validationMessage;
    
    // Reset classi CSS solo se non ci sono errori nativi
    if (isNativelyValid) {
        amountInput.classList.remove('is-invalid');
    }
    
    if (!isNativelyValid) {
        // Errore di validazione nativa (step, min, max, etc.) - Mostra hint nativi
        feedbackDiv.className = 'form-text text-warning';
        feedbackDiv.innerHTML = `<i class="fas fa-info-circle me-1"></i>${nativeValidationMessage}`;
        errorDiv.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.classList.add('disabled');
        return; // Esce presto per lasciare che il browser gestisca la validazione
    } else if (!amount || amount <= 0) {
        // Campo vuoto
        feedbackDiv.className = 'form-text text-muted';
        feedbackDiv.textContent = 'Inserisci un importo tra €0.01 e €49,999.99';
        errorDiv.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.classList.add('disabled');
    } else if (amount >= 50000) {
        // Importo troppo alto - ERRORE CUSTOMIZZATO
        amountInput.classList.add('is-invalid');
        errorDiv.style.display = 'block';
        feedbackDiv.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.classList.add('disabled');
    } else if (amount >= 0.01 && amount < 50000) {
        // Importo valido - OK
        amountInput.classList.add('is-valid');
        errorDiv.style.display = 'none';
        feedbackDiv.className = 'form-text text-success';
        feedbackDiv.style.display = 'block';
        feedbackDiv.innerHTML = `<i class="fas fa-check-circle me-1"></i>Importo valido: €${amount.toLocaleString('it-IT', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        submitBtn.disabled = false;
        submitBtn.classList.remove('disabled');
    }
}

// Event listener per validazione in tempo reale
document.getElementById('amount')?.addEventListener('input', validateDepositAmount);
document.getElementById('amount')?.addEventListener('change', validateDepositAmount);
document.getElementById('amount')?.addEventListener('invalid', validateDepositAmount); // Per hint nativi

// Inizializza validazione quando si apre il modal deposito
document.getElementById('depositModal')?.addEventListener('shown.bs.modal', function() {
    const submitBtn = document.getElementById('deposit-submit-btn');
    const amountInput = document.getElementById('amount');
    
    if (submitBtn) {
        submitBtn.disabled = true; // Inizialmente disabilitato
        submitBtn.classList.add('disabled');
    }
    
    if (amountInput) {
        amountInput.value = ''; // Reset campo
        amountInput.focus(); // Focus automatico
    }
    
    validateDepositAmount();
});

// Reset quando si chiude il modal
document.getElementById('depositModal')?.addEventListener('hidden.bs.modal', function() {
    const amountInput = document.getElementById('amount');
    const errorDiv = document.getElementById('deposit-amount-error');
    const feedbackDiv = document.getElementById('deposit-amount-feedback');
    
    if (amountInput) {
        amountInput.value = '';
        amountInput.classList.remove('is-invalid', 'is-valid');
    }
    
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
    
    if (feedbackDiv) {
        feedbackDiv.className = 'form-text text-muted';
        feedbackDiv.textContent = 'Inserisci un importo tra €0.01 e €49,999.99';
        feedbackDiv.style.display = 'block';
    }
});

// Previeni submit del form solo per il limite €50,000 (mantiene validazione nativa per altri errori)
document.querySelector('#depositModal form')?.addEventListener('submit', function(e) {
    const amountInput = document.getElementById('amount');
    const amount = parseFloat(amountInput.value);
    
    // Solo blocca se supera €50,000, altrimenti lascia che il browser gestisca gli altri errori
    if (amount >= 50000) {
        e.preventDefault();
        validateDepositAmount();
        return false;
    }
    
    // Per altri errori di validazione, lascia che il browser mostri i suoi hint
    if (!amountInput.validity.valid) {
        // Non fare preventDefault - lascia che il browser mostri gli hint nativi
        validateDepositAmount();
        return false;
    }
});
</script>
@endsection