@extends('layouts.bootstrap')

@section('title', 'Nuovo Bonifico')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="text-white"><i class="fas fa-exchange-alt me-2"></i>Nuovo Bonifico</h4>
                        <a href="{{ route('dashboard.cliente') }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Torna alla Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li class="text-white">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Informazioni conto -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-dark border-secondary">
                                <div class="card-body">
                                    <h6 class="card-title text-info">Il tuo conto</h6>
                                    <p class="mb-1 text-white"><strong>IBAN:</strong> {{ $account->iban }}</p>
                                    <p class="mb-0 text-white"><strong>Saldo disponibile:</strong> 
                                        <span class="text-success">€{{ number_format($account->balance, 2, ',', '.') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('client.transfer.store') }}" id="transferForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="recipient_iban" class="form-label text-white">IBAN Beneficiario *</label>
                                <input type="text" 
                                       class="form-control @error('recipient_iban') is-invalid @enderror" 
                                       id="recipient_iban" 
                                       name="recipient_iban" 
                                       value="{{ old('recipient_iban') }}" 
                                       placeholder="IT60 X054 2811 1010 0000 0123 456"
                                       maxlength="34"
                                       required>
                                <div class="form-text text-white">
                                    Inserisci l'IBAN del beneficiario<br>
                                    <small id="iban-length" class="text-muted">Caratteri inseriti (ne servono 27 per un iban italiano valido): 0</small>
                                </div>
                                @error('recipient_iban')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="beneficiary_name" class="form-label text-white">Nome Beneficiario</label>
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
                                <label for="amount" class="form-label text-white">Importo (€) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" 
                                           name="amount" 
                                           value="{{ old('amount') }}" 
                                           step="0.01" 
                                           min="0.01" 
                                           max="50000"
                                           placeholder="0,00"
                                           required>
                                </div>
                                <div class="form-text text-white">Importo minimo: €0,01 - Massimo: €50.000,00</div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label text-white">Causale *</label>
                            <input type="text" 
                                   class="form-control @error('description') is-invalid @enderror" 
                                   id="description" 
                                   name="description" 
                                   value="{{ old('description') }}" 
                                   placeholder="Inserisci la causale del bonifico"
                                   maxlength="255"
                                   pattern="[A-Za-z0-9\s\-_.,!?()]+"
                                   title="Solo lettere, numeri e punteggiatura di base"
                                   required>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Riepilogo dinamico -->
                        <div id="summary" class="card bg-info bg-opacity-10 border-info mb-3" style="display: none;">
                            <div class="card-body">
                                <h6 class="card-title text-info">Riepilogo Bonifico</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1 text-white"><strong>Beneficiario:</strong> <span id="summary-beneficiary">-</span></p>
                                        <p class="mb-1 text-white"><strong>IBAN:</strong> <span id="summary-iban">-</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1 text-white"><strong>Importo:</strong> <span id="summary-amount" class="text-warning">€0,00</span></p>
                                        <p class="mb-0 text-white"><strong>Causale:</strong> <span id="summary-description">-</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conferma diretta -->
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong class="text-dark">Attenzione:</strong> 
                            <span class="text-dark">Il bonifico verrà eseguito immediatamente dopo aver cliccato "Esegui Bonifico". Verifica attentamente tutti i dati.</span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('dashboard.cliente') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annulla
                            </a>
                            <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                                <i class="fas fa-check me-1"></i>Esegui Bonifico
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
    
    // Formatta IBAN durante la digitazione
    ibanInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '').toUpperCase();
        let formatted = value.replace(/(.{4})/g, '$1 ').trim();
        e.target.value = formatted;
        
        // Aggiorna contatore caratteri
        const lengthCounter = document.getElementById('iban-length');
        const cleanValue = value.replace(/\s/g, '');
        lengthCounter.textContent = `Caratteri inseriti (ne servono 27 per un iban italiano valido): ${cleanValue.length}`;
        
        // Colora il contatore in base alla validità
        if (cleanValue.length === 0) {
            lengthCounter.className = 'text-muted';
        } else if (cleanValue.startsWith('IT') && cleanValue.length === 27) {
            lengthCounter.className = 'text-success';
            lengthCounter.textContent += ' ✓ IBAN italiano valido';
        } else if (cleanValue.length >= 15 && cleanValue.length <= 34) {
            lengthCounter.className = 'text-warning';
            lengthCounter.textContent += ' (verificare validità)';
        } else {
            lengthCounter.className = 'text-danger';
            lengthCounter.textContent += ' ✗ Lunghezza non valida';
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

        const clientIban = '{{ $account->iban }}';
        const ibanEqual = iban.replace(/\s/g, '') === clientIban.replace(/\s/g, '');
        
        if (iban && amount && description && !ibanEqual) {
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
            errorDiv.textContent = 'Non puoi inviare un bonifico al tuo stesso conto';
            ibanInput.classList.add('is-invalid');
        } else {
            errorDiv.style.display = 'none';
            ibanInput.classList.remove('is-invalid');
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
    
    // Validazione prima dell'invio con conferma diretta
    form.addEventListener('submit', function(e) {
        const amount = parseFloat(amountInput.value);
        const maxAmount = {{ $account->balance }};
        
        if (amount > maxAmount) {
            e.preventDefault();
            alert('L\'importo supera il saldo disponibile.');
            return false;
        }
        
        const confirmMessage = `Sei sicuro di voler eseguire questo bonifico?\n\n` +
                              `Beneficiario: ${beneficiaryInput.value || 'Non specificato'}\n` +
                              `IBAN: ${ibanInput.value}\n` +
                              `Importo: €${parseFloat(amountInput.value).toFixed(2)}\n` +
                              `Causale: ${descriptionInput.value}\n\n` +
                              `L'operazione verrà eseguita immediatamente e non potrà essere annullata.`;
        
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

