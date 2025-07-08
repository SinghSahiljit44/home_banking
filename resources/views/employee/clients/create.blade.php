@extends('layouts.bootstrap')

@section('title', 'Registra Nuovo Cliente')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-user-plus me-2"></i>Registra Nuovo Cliente</h4>
                        <a href="{{ route('employee.clients.index') }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Torna alla Lista
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

                    <form method="POST" action="{{ route('employee.clients.store') }}" id="createClientForm">
                        @csrf
                        
                        <!-- Informazioni Base -->
                        <div class="card bg-dark border-secondary mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-id-card me-2"></i>Informazioni Personali</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">Nome *</label>
                                        <input type="text" 
                                               class="form-control @error('first_name') is-invalid @enderror" 
                                               id="first_name" 
                                               name="first_name" 
                                               value="{{ old('first_name') }}" 
                                               required 
                                               maxlength="50">
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Cognome *</label>
                                        <input type="text" 
                                               class="form-control @error('last_name') is-invalid @enderror" 
                                               id="last_name" 
                                               name="last_name" 
                                               value="{{ old('last_name') }}" 
                                               required 
                                               maxlength="50">
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">Username *</label>
                                        <input type="text" 
                                               class="form-control @error('username') is-invalid @enderror" 
                                               id="username" 
                                               name="username" 
                                               value="{{ old('username') }}" 
                                               required 
                                               maxlength="50">
                                        @error('username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Username univoco per il login</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email') }}" 
                                               required 
                                               maxlength="100">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Telefono</label>
                                        <input type="tel" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone') }}" 
                                               maxlength="20">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Indirizzo</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3" 
                                              maxlength="500">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="auto_assign" value="1">

                        <!-- Opzioni Conto -->
                        <div class="card bg-dark border-secondary mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-university me-2"></i>Opzioni Conto Corrente</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="create_account" 
                                               name="create_account" 
                                               value="1" 
                                               {{ old('create_account') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="create_account">
                                            Crea conto corrente per questo cliente
                                        </label>
                                    </div>
                                    <div class="form-text">Se abilitato, verrà creato automaticamente un conto corrente</div>
                                </div>

                                <div class="mb-3" id="initialBalanceGroup" style="display: none;">
                                    <label for="initial_balance" class="form-label">Saldo Iniziale (€)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">€</span>
                                        <input type="number" 
                                               class="form-control @error('initial_balance') is-invalid @enderror" 
                                               id="initial_balance" 
                                               name="initial_balance" 
                                               value="{{ old('initial_balance', '0.00') }}" 
                                               step="0.01" 
                                               min="0">
                                    </div>
                                    @error('initial_balance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Importo da depositare nel nuovo conto</div>
                                </div>
                            </div>
                        </div>

                        <!-- Conferma -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Nota:</strong> Verrà generata automaticamente una password temporanea che dovrai comunicare al cliente. 
                            Il cliente potrà cambiarla al primo accesso.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('employee.clients.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annulla
                            </a>
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-save me-1"></i>Registra Cliente
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
    const createAccountCheck = document.getElementById('create_account');
    const initialBalanceGroup = document.getElementById('initialBalanceGroup');
    
    // Mostra campo saldo iniziale se si crea il conto
    createAccountCheck.addEventListener('change', function() {
        if (this.checked) {
            initialBalanceGroup.style.display = 'block';
        } else {
            initialBalanceGroup.style.display = 'none';
        }
    });
    
    // Genera username automatico da nome e cognome
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const usernameInput = document.getElementById('username');
    
    function generateUsername() {
        const firstName = firstNameInput.value.toLowerCase().replace(/[^a-z]/g, '');
        const lastName = lastNameInput.value.toLowerCase().replace(/[^a-z]/g, '');
        
        if (firstName && lastName) {
            usernameInput.value = firstName + '.' + lastName;
        }
    }
    
    firstNameInput.addEventListener('blur', generateUsername);
    lastNameInput.addEventListener('blur', generateUsername);
    
    // Inizializza la visualizzazione
    if (createAccountCheck.checked) {
        initialBalanceGroup.style.display = 'block';
    }
});
</script>
@endsection