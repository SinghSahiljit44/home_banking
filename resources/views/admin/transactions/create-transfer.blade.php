@extends('layouts.bootstrap')

@section('title', 'Bonifico per Cliente')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-money-bill-transfer me-2"></i>Bonifico per {{ $client->full_name }}</h4>
                        <a href="{{ route('admin.users.show', $client) }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Torna al Cliente
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Informazioni Cliente -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-dark border-secondary">
                                <div class="card-body">
                                    <h6 class="card-title text-info">Cliente</h6>
                                    <p class="mb-1"><strong>Nome:</strong> {{ $client->full_name }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $client->email }}</p>
                                    <p class="mb-0"><strong>Username:</strong> {{ $client->username }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-dark border-secondary">
                                <div class="card-body">
                                    <h6 class="card-title text-info">Conto</h6>
                                    <p class="mb-1"><strong>IBAN:</strong> {{ $client->account->iban }}</p>
                                    <p class="mb-1"><strong>Numero:</strong> {{ $client->account->account_number }}</p>
                                    <p class="mb-0"><strong>Saldo disponibile:</strong> 
                                        <span class="text-success">€{{ number_format($client->account->balance, 2, ',', '.') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.transactions.create-transfer', $client) }}" id="transferForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="recipient_iban" class="form-label">IBAN Beneficiario *</label>
                                <input type="text" 
                                       class="form-control @error('recipient_iban') is-invalid @enderror" 
                                       id="recipient_iban" 
                                       name="recipient_iban" 
                                       value="{{ old('recipient_iban') }}" 
                                       placeholder="IT60 X054 2811 1010 0000 0123 456"
                                       maxlength="34"
                                       required>
                                <div class="form-text">
                                    Inserisci l'IBAN del beneficiario<br>
                                    <small id="iban-length" class="text-muted">Caratteri inseriti (ne servono 27 per un iban italiano valido): 0</small>
                                </div>
                                @error('recipient_iban')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="beneficiary_name" class="form-label">Nome Beneficiario</label>
                                <input type="text" 
                                       class="form-control @error('beneficiary_name') is-invalid @enderror" 
                                       id="beneficiary_name" 
                                       name="beneficiary_name" 
                                       value="{{ old('beneficiary_name') }}" 
                                       placeholder="Nome del beneficiario"
                                       maxlength="100">
                                @error('beneficiary_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Importo (€) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" 
                                           name="amount" 
                                           value="{{ old('amount') }}" 
                                           step="0.01" 
                                           min="0.01" 
                                           max="100000"
                                           placeholder="0,00"
                                           required>
                                </div>
                                <div class="form-text">
                                    Saldo disponibile: €{{ number_format($client->account->balance, 2, ',', '.') }}
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Causale *</label>
                            <input type="text" 
                                   class="form-control @error('description') is-invalid @enderror" 
                                   id="description" 
                                   name="description" 
                                   value="{{ old('description') }}" 
                                   placeholder="Inserisci la causale del bonifico"
                                   maxlength="255"
                                   required>
                            <div class="form-text">Verrà aggiunto automaticamente "Operazione Admin: {{ Auth::user()->full_name }}"</div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Riepilogo dinamico -->
                        <div id="summary" class="card bg-warning bg-opacity-10 border-warning mb-3" style="display: none;">
                            <div class="card-body">
                                <h6 class="card-title text-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Riepilogo Bonifico Amministrativo
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Da Cliente:</strong> {{ $client->full_name }}</p>
                                        <p class="mb-1"><strong>Beneficiario:</strong> <span id="summary-beneficiary">-</span></p>
                                        <p class="mb-1"><strong>IBAN:</strong> <span id="summary-iban">-</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Importo:</strong> <span id="summary-amount" class="text-danger">€0,00</span></p>
                                        <p class="mb-1"><strong>Causale:</strong> <span id="summary-description">-</span></p>
                                        <p class="mb-0"><strong>Operatore:</strong> {{ Auth::user()->full_name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conferma amministrativa -->
                        <div class="alert alert-danger">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>Operazione Amministrativa:</strong> 
                            Stai creando un bonifico per conto del cliente {{ $client->full_name }}. 
                            L'operazione verrà eseguita immediatamente e sarà tracciata nei log di sistema.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.show', $client) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annulla
                            </a>
                            <button type="submit" class="btn btn-danger" id="submitBtn" disabled>
                                <i class="fas fa-money-bill-transfer me-1"></i>Esegui Bonifico Amministrativo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('transferForm');
    const summary = document.getElementById('summary');
    const submitBtn = document.getElementById('submitBtn');
    
    // Elementi del riepilogo
    const summaryBeneficiary = document.getElementById('summary-beneficiary');
    const summaryIban = document.getElementById('summary-iban');
    const summaryAmount = document.getElementById('summary-amount');
    const summaryDescription = document.getElementById('summary-description');
    
    // Input
    const ibanInput = document.getElementById('recipient_iban');
    const beneficiaryInput = document.getElementById('beneficiary_name');
    const amountInput = document.getElementById('amount');
    const descriptionInput = document.getElementById('description');
    const lengthCounter = document.getElementById('iban-length');
    
    // Formatta IBAN durante la digitazione - VALIDAZIONE IDENTICA AI MIEI CLIENTI
    ibanInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '').toUpperCase();
        let formatted = value.replace(/(.{4})/g, '$1 ').trim();
        e.target.value = formatted;
        
        const cleanValue = value.replace(/\s/g, '');
        
        // Aggiorna contatore caratteri
        lengthCounter.textContent = `Caratteri inseriti (ne servono 27 per un iban italiano valido): ${cleanValue.length}`;
        
        // Controllo semplice e diretto del formato
        let hasError = false;
        let errorMessage = '';
        
        // Se ci sono almeno 2 caratteri, controlla che siano lettere
        if (cleanValue.length >= 2) {
            const char1 = cleanValue.charAt(0);
            const char2 = cleanValue.charAt(1);
            
            if (!(char1 >= 'A' && char1 <= 'Z') || !(char2 >= 'A' && char2 <= 'Z')) {
                hasError = true;
                errorMessage = 'I primi due caratteri devono essere lettere (codice paese)';
            }
        }
        
        // Se ci sono almeno 4 caratteri e i primi 2 sono ok, controlla che il 3° e 4° siano cifre
        if (cleanValue.length >= 4 && !hasError) {
            const char3 = cleanValue.charAt(2);
            const char4 = cleanValue.charAt(3);
            
            if (!(char3 >= '0' && char3 <= '9') || !(char4 >= '0' && char4 <= '9')) {
                hasError = true;
                errorMessage = 'Il terzo e quarto carattere devono essere cifre (codice controllo)';
            }
        }
        
        // Mostra errore e disabilita pulsante se necessario
        if (hasError) {
            lengthCounter.className = 'text-danger';
            lengthCounter.textContent += ` ✗ ${errorMessage}`;
            ibanInput.classList.add('is-invalid');
        } else {
            // Nessun errore di formato, usa la logica normale
            ibanInput.classList.remove('is-invalid');
            
            if (cleanValue.length === 0) {
                lengthCounter.className = 'text-muted';
            } else if (cleanValue.startsWith('IT') && cleanValue.length === 27) {
                lengthCounter.className = 'text-success';
                lengthCounter.textContent += ' ✓ IBAN italiano valido';
            } else if (cleanValue.length >= 15 && cleanValue.length <= 34) {
                lengthCounter.className = 'text-warning';
                lengthCounter.textContent += ' (verificare validità del paese)';
            } else if (cleanValue.length > 0 && cleanValue.length < 15) {
                lengthCounter.className = 'text-danger';
                lengthCounter.textContent += ' ✗ Lunghezza non valida per un IBAN';
                ibanInput.classList.add('is-invalid');
            }
        }
        
        updateSummary();
    });
    
    // Aggiorna riepilogo in tempo reale
    [beneficiaryInput, amountInput, descriptionInput].forEach(input => {
        input.addEventListener('input', updateSummary);
    });
    
    function updateSummary() {
        const iban = ibanInput.value;
        const beneficiary = beneficiaryInput.value || 'Non specificato';
        const amount = amountInput.value;
        const description = descriptionInput.value;

        const clientIban = '{{ $client->account->iban }}';
        const ibanEqual = iban.replace(/\s/g, '') === clientIban.replace(/\s/g, '');
        
        // Controlla se l'IBAN ha errori di validazione
        const cleanValue = iban.replace(/\s/g, '');
        let hasValidationError = false;
        
        if (cleanValue.length >= 2) {
            const char1 = cleanValue.charAt(0);
            const char2 = cleanValue.charAt(1);
            if (!(char1 >= 'A' && char1 <= 'Z') || !(char2 >= 'A' && char2 <= 'Z')) {
                hasValidationError = true;
            }
        }
        
        if (cleanValue.length >= 4 && !hasValidationError) {
            const char3 = cleanValue.charAt(2);
            const char4 = cleanValue.charAt(3);
            if (!(char3 >= '0' && char3 <= '9') || !(char4 >= '0' && char4 <= '9')) {
                hasValidationError = true;
            }
        }
        
        if (cleanValue.length > 0 && cleanValue.length < 15) {
            hasValidationError = true;
        }
        
        if (iban && amount && description && !ibanEqual && !hasValidationError) {
            summaryBeneficiary.textContent = beneficiary;
            summaryIban.textContent = iban;
            summaryAmount.textContent = '€' + parseFloat(amount || 0).toFixed(2).replace('.', ',');
            summaryDescription.textContent = description;
            summary.style.display = 'block';
            submitBtn.disabled = false;
        } else {
            summary.style.display = 'none';
            submitBtn.disabled = true;
        }

        const errorDiv = document.getElementById('iban-error') || createErrorDiv();

        if (ibanEqual && iban) {
            errorDiv.style.display = 'block';
            errorDiv.textContent = 'L\'IBAN destinatario non può essere uguale a quello del mittente';
            ibanInput.classList.add('is-invalid');
        } else {
            errorDiv.style.display = 'none';
            if (!hasValidationError) {
                ibanInput.classList.remove('is-invalid');
            }
        }
    }
    
    function createErrorDiv() {
        const errorDiv = document.createElement('div');
        errorDiv.id = 'iban-error';
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.style.display = 'none';
        ibanInput.parentNode.appendChild(errorDiv);
        return errorDiv;
    }

    // Validazione prima dell'invio
    form.addEventListener('submit', function(e) {
        const amount = parseFloat(amountInput.value);
        const maxAmount = {{ $client->account->balance }};
        
        if (amount > maxAmount) {
            e.preventDefault();
            alert('L\'importo supera il saldo disponibile del cliente.');
            return false;
        }
        
        const confirmMessage = `CONFERMA OPERAZIONE AMMINISTRATIVA\n\n` +
                              `Stai per eseguire un bonifico per conto di:\n` +
                              `Cliente: {{ $client->full_name }}\n` +
                              `Beneficiario: ${beneficiaryInput.value || 'Non specificato'}\n` +
                              `IBAN: ${ibanInput.value}\n` +
                              `Importo: €${parseFloat(amountInput.value).toFixed(2)}\n` +
                              `Causale: ${descriptionInput.value}\n\n` +
                              `L'operazione verrà tracciata nei log di sistema.\n` +
                              `Confermi l'esecuzione?`;
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
        
        // Disabilita il pulsante per evitare doppi invii
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Elaborazione in corso...';
        
        return true;
    });
    
    // Inizializza
    updateSummary();
});
</script>
@endsection