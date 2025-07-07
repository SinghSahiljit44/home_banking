@extends('layouts.bootstrap')

@section('title', 'Crea Prelievo')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-minus-circle me-2"></i>Crea Prelievo per {{ $client->full_name }}</h4>
                        <a href="{{ route('admin.users.show', $client) }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Torna al Profilo
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

                    <!-- Informazioni Cliente e Conto -->
                    <div class="card bg-dark border-secondary mb-4">
                        <div class="card-header">
                            <h6><i class="fas fa-user me-2"></i>Informazioni Cliente</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nome:</strong> {{ $client->full_name }}</p>
                                    <p><strong>Email:</strong> {{ $client->email }}</p>
                                    <p><strong>Numero Conto:</strong> {{ $client->account->account_number }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>IBAN:</strong> <span class="font-monospace">{{ $client->account->iban }}</span></p>
                                    <p><strong>Saldo Attuale:</strong> 
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

                    @if(!$client->account->is_active)
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attenzione:</strong> Il conto del cliente è bloccato. Non è possibile effettuare prelievi.
                        </div>
                    @elseif($client->account->balance <= 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attenzione:</strong> Il conto del cliente non ha saldo disponibile per prelievi.
                        </div>
                    @else
                        <!-- Form Prelievo -->
                        <form method="POST" action="{{ route('admin.users.withdrawal', $client) }}" id="withdrawalForm">
                            @csrf
                            
                            <div class="card bg-dark border-secondary mb-4">
                                <div class="card-header">
                                    <h6><i class="fas fa-form me-2"></i>Dettagli Prelievo</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="amount" class="form-label">Importo da Prelevare (€) *</label>
                                            <input type="number" 
                                                   class="form-control @error('amount') is-invalid @enderror" 
                                                   id="amount" 
                                                   name="amount" 
                                                   step="0.01" 
                                                   min="0.01" 
                                                   max="{{ $client->account->balance }}"
                                                   value="{{ old('amount') }}" 
                                                   required>
                                            <div class="form-text">
                                                Importo massimo prelevabile: €{{ number_format($client->account->balance, 2, ',', '.') }}
                                            </div>
                                            @error('amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="description" class="form-label">Descrizione *</label>
                                            <input type="text" 
                                                   class="form-control @error('description') is-invalid @enderror" 
                                                   id="description" 
                                                   name="description" 
                                                   value="{{ old('description', 'Prelievo per ' . $client->full_name) }}" 
                                                   maxlength="255"
                                                   required>
                                            <div class="form-text">Descrizione del prelievo (max 255 caratteri)</div>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Anteprima Calcoli -->
                                    <div class="alert alert-info" id="previewCalculation" style="display: none;">
                                        <h6><i class="fas fa-calculator me-2"></i>Anteprima Operazione</h6>
                                        <div class="row text-center">
                                            <div class="col-md-4">
                                                <strong>Saldo Attuale</strong><br>
                                                <span class="h6 text-info">€{{ number_format($client->account->balance, 2, ',', '.') }}</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Importo Prelievo</strong><br>
                                                <span class="h6 text-danger" id="previewAmount">€0,00</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Saldo Dopo Prelievo</strong><br>
                                                <span class="h6 text-success" id="previewNewBalance">€{{ number_format($client->account->balance, 2, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Conferma e Sicurezza -->
                            <div class="card bg-dark border-warning mb-4">
                                <div class="card-header bg-warning text-dark">
                                    <h6><i class="fas fa-shield-alt me-2"></i>Conferma di Sicurezza</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Attenzione:</strong> Stai per prelevare denaro dal conto di {{ $client->full_name }}. 
                                        Questa operazione verrà registrata nei log di sistema e sarà tracciabile negli audit.
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="confirmOperation" required>
                                        <label class="form-check-label" for="confirmOperation">
                                            Confermo di voler eseguire questa operazione di prelievo per conto di {{ $client->full_name }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Pulsanti Azione -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.users.show', $client) }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Annulla
                                </a>
                                <button type="submit" class="btn btn-danger" id="submitBtn" disabled>
                                    <i class="fas fa-minus-circle me-1"></i>Esegui Prelievo
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const confirmCheckbox = document.getElementById('confirmOperation');
    const submitBtn = document.getElementById('submitBtn');
    const previewCalculation = document.getElementById('previewCalculation');
    const previewAmount = document.getElementById('previewAmount');
    const previewNewBalance = document.getElementById('previewNewBalance');
    
    const maxBalance = {{ $client->account->balance }};
    
    function updatePreview() {
        const amount = parseFloat(amountInput.value) || 0;
        
        if (amount > 0) {
            previewCalculation.style.display = 'block';
            previewAmount.textContent = '€' + amount.toLocaleString('it-IT', {minimumFractionDigits: 2});
            
            const newBalance = maxBalance - amount;
            previewNewBalance.textContent = '€' + newBalance.toLocaleString('it-IT', {minimumFractionDigits: 2});
            
            // Cambia colore in base al saldo risultante
            if (newBalance < 0) {
                previewNewBalance.className = 'h6 text-danger';
            } else if (newBalance < 100) {
                previewNewBalance.className = 'h6 text-warning';
            } else {
                previewNewBalance.className = 'h6 text-success';
            }
        } else {
            previewCalculation.style.display = 'none';
        }
    }
    
    function validateForm() {
        const amount = parseFloat(amountInput.value) || 0;
        const isValidAmount = amount > 0 && amount <= maxBalance;
        const isConfirmed = confirmCheckbox.checked;
        
        submitBtn.disabled = !(isValidAmount && isConfirmed);
    }
    
    // Validazione importo
    amountInput.addEventListener('input', function(e) {
        const amount = parseFloat(e.target.value);
        
        if (amount > maxBalance) {
            e.target.setCustomValidity('Importo superiore al saldo disponibile');
        } else if (amount <= 0) {
            e.target.setCustomValidity('Importo deve essere maggiore di 0');
        } else {
            e.target.setCustomValidity('');
        }
        
        updatePreview();
        validateForm();
    });
    
    confirmCheckbox.addEventListener('change', validateForm);
    
    // Conferma prima dell'invio
    document.getElementById('withdrawalForm').addEventListener('submit', function(e) {
        const amount = parseFloat(amountInput.value);
        const formattedAmount = amount.toLocaleString('it-IT', {minimumFractionDigits: 2});
        
        if (!confirm(`Sei sicuro di voler prelevare €${formattedAmount} dal conto di {{ $client->full_name }}?`)) {
            e.preventDefault();
        }
    });
});
</script>
@endsection