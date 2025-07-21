@extends('layouts.bootstrap')

@section('title', 'Crea Nuovo Utente')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-user-plus me-2"></i>Crea Nuovo Utente</h4>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Torna alla Lista
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Errori di validazione:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm">
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
                                               maxlength="100"
                                               minlength="5">
                                        <div class="form-text">Inserisci un indirizzo email valido (es. nome@dominio.com)</div>
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
                                               placeholder="1234567890"
                                               maxlength="10">
                                        <div class="form-text">Inserisci esattamente 10 cifre</div>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="role" class="form-label">Ruolo *</label>
                                        <select class="form-select @error('role') is-invalid @enderror" 
                                                id="role" 
                                                name="role" 
                                                required>
                                            <option value="">Seleziona ruolo</option>
                                            <option value="client" {{ old('role') === 'client' ? 'selected' : '' }}>Cliente</option>
                                            <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Dipendente</option>
                                        </select>
                                        @error('role')
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

                        <!-- Opzioni Conto (solo per clienti) -->
                        <div class="card bg-dark border-secondary mb-4" id="accountOptions" style="display: none;">
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
                                               min="0"
                                               max="1000000">
                                    </div>
                                    @error('initial_balance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Importo da depositare nel nuovo conto (massimo €1.000.000)</div>
                                </div>
                            </div>
                        </div>

                        <!-- Conferma -->
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attenzione:</strong> Verifica attentamente tutti i dati prima di procedere. 
                            Se non imposti una password personalizzata, ne verrà generata una automaticamente che dovrai comunicare all'utente.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annulla
                            </a>
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-save me-1"></i>Crea Utente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const accountOptions = document.getElementById('accountOptions');
    const createAccountCheck = document.getElementById('create_account');
    const initialBalanceGroup = document.getElementById('initialBalanceGroup');
    const customPasswordCheck = document.getElementById('custom_password');
    const passwordGroup = document.getElementById('passwordGroup');
    const passwordInput = document.getElementById('password');
    
    // Mostra opzioni conto solo per clienti
    roleSelect.addEventListener('change', function() {
        if (this.value === 'client') {
            accountOptions.style.display = 'block';
        } else {
            accountOptions.style.display = 'none';
            createAccountCheck.checked = false;
            initialBalanceGroup.style.display = 'none';
        }
    });
    
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
        if (!usernameInput.value || usernameInput.value === usernameInput.getAttribute('data-generated')) {
            const firstName = firstNameInput.value.toLowerCase().replace(/[^a-z]/g, '');
            const lastName = lastNameInput.value.toLowerCase().replace(/[^a-z]/g, '');
            
            if (firstName && lastName) {
                const generatedUsername = firstName + '.' + lastName;
                usernameInput.value = generatedUsername;
                usernameInput.setAttribute('data-generated', generatedUsername);
            }
        }
    }
    
    firstNameInput.addEventListener('blur', generateUsername);
    lastNameInput.addEventListener('blur', generateUsername);
    
    // NUOVE VALIDAZIONI EMAIL 
    const emailInput = document.getElementById('email');
    
    // Funzione di validazione email
    function validateEmail(email) {
        const trimmedEmail = email.trim();
        
        // Controlli base
        if (!trimmedEmail) return false;
        if (!trimmedEmail.includes('@')) return false;
        if (trimmedEmail.indexOf('@') !== trimmedEmail.lastIndexOf('@')) return false;
        
        const parts = trimmedEmail.split('@');
        if (parts.length !== 2) return false;
        
        const localPart = parts[0];
        const domainPart = parts[1];
        
        // Parte locale non può essere vuota
        if (!localPart || localPart.length === 0) return false;
        
        // Parte dominio non può essere vuota
        if (!domainPart || domainPart.length === 0) return false;
        
        // Il dominio DEVE contenere almeno un punto
        if (!domainPart.includes('.')) return false;
        
        // Il dominio non può iniziare o finire con un punto
        if (domainPart.startsWith('.') || domainPart.endsWith('.')) return false;
        
        // Controlla che dopo l'ultimo punto ci siano almeno 2 caratteri (estensione)
        const lastDotIndex = domainPart.lastIndexOf('.');
        const extension = domainPart.substring(lastDotIndex + 1);
        
        if (extension.length < 2) return false;
        
        // Controlla che prima dell'ultimo punto ci sia almeno un carattere (nome dominio)
        const domainName = domainPart.substring(0, lastDotIndex);
        if (domainName.length < 1) return false;
        
        return true;
    }
    
    // Funzione per determinare il messaggio di errore email specifico
    function getEmailErrorMessage(email) {
        const trimmedEmail = email.trim();
        
        if (!trimmedEmail.includes('@')) {
            return 'L\'email deve contenere il simbolo @';
        }
        
        if (trimmedEmail.indexOf('@') !== trimmedEmail.lastIndexOf('@')) {
            return 'L\'email deve contenere esattamente un simbolo @';
        }
        
        const parts = trimmedEmail.split('@');
        const localPart = parts[0];
        const domainPart = parts[1];
        
        if (!localPart) {
            return 'Inserisci la parte prima della @';
        }
        
        if (!domainPart) {
            return 'Inserisci il dominio dopo la @';
        }
        
        if (!domainPart.includes('.')) {
            return 'Il dominio deve contenere un\'estensione (es. .com, .it)';
        }
        
        if (domainPart.startsWith('.')) {
            return 'Il dominio non può iniziare con un punto';
        }
        
        if (domainPart.endsWith('.')) {
            return 'Il dominio non può terminare con un punto';
        }
        
        const lastDotIndex = domainPart.lastIndexOf('.');
        const extension = domainPart.substring(lastDotIndex + 1);
        const domainName = domainPart.substring(0, lastDotIndex);
        
        if (domainName.length < 1) {
            return 'Il nome del dominio non può essere vuoto';
        }
        
        if (extension.length < 2) {
            return 'L\'estensione del dominio deve avere almeno 2 caratteri';
        }
        
        return 'Inserisci un indirizzo email valido (es. nome@dominio.com)';
    }
    
    // Funzioni per gestire errori email
    function showEmailError(message) {
        const errorDiv = document.getElementById('email-error') || createEmailErrorDiv();
        
        errorDiv.textContent = message;
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.style.display = 'block';
        emailInput.classList.add('is-invalid');
        emailInput.classList.remove('is-valid');
    }

    function clearEmailError() {
        const errorDiv = document.getElementById('email-error');
        
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
        emailInput.classList.remove('is-invalid');
        emailInput.classList.add('is-valid');
    }
    
    function createEmailErrorDiv() {
        const errorDiv = document.createElement('div');
        errorDiv.id = 'email-error';
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.style.display = 'none';
        emailInput.parentNode.appendChild(errorDiv);
        return errorDiv;
    }
    
    // Funzione di validazione email
    function validateEmailField() {
        const email = emailInput.value;
        
        if (!email) {
            clearEmailError();
            emailInput.classList.remove('is-valid', 'is-invalid');
            return;
        }

        if (!validateEmail(email)) {
            const errorMessage = getEmailErrorMessage(email);
            showEmailError(errorMessage);
        } else {
            clearEmailError();
        }
    }
    
    // Event listeners per email
    emailInput.addEventListener('input', validateEmailField);
    emailInput.addEventListener('blur', validateEmailField);
    
    // NUOVE VALIDAZIONI TELEFONO 
    const phoneInput = document.getElementById('phone');
    
    phoneInput.addEventListener('input', function(e) {
        // Rimuovi tutti i caratteri non numerici
        let value = e.target.value.replace(/[^\d]/g, '');
        
        // Limita a 10 cifre
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        
        e.target.value = value;
        validatePhone();
    });
    
    function validatePhone() {
        const phone = phoneInput.value.trim();
        let errorDiv = document.getElementById('phone-error') || createPhoneErrorDiv();
        
        if (phone === '') {
            errorDiv.style.display = 'none';
            phoneInput.classList.remove('is-invalid', 'is-valid');
            return;
        }
        
        let isValid = false;
        let message = '';
        
        if (phone.length === 10 && /^\d{10}$/.test(phone)) {
            isValid = true;
            message = '✓ Numero valido (10 cifre)';
        } else if (phone.length < 10) {
            message = `✗ Inserisci 10 cifre (ne hai inserite ${phone.length})`;
        } else {
            message = '✗ Il numero deve essere di esattamente 10 cifre';
        }
        
        if (isValid) {
            phoneInput.classList.remove('is-invalid');
            phoneInput.classList.add('is-valid');
            errorDiv.className = 'form-text text-success';
        } else {
            phoneInput.classList.remove('is-valid');
            phoneInput.classList.add('is-invalid');
            errorDiv.className = 'invalid-feedback d-block';
        }
        
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
    
    function createPhoneErrorDiv() {
        const errorDiv = document.createElement('div');
        errorDiv.id = 'phone-error';
        errorDiv.style.display = 'none';
        phoneInput.parentNode.appendChild(errorDiv);
        return errorDiv;
    }
    
    // Inizializza la visualizzazione
    if (roleSelect.value === 'client') {
        accountOptions.style.display = 'block';
    }
    
    if (createAccountCheck.checked) {
        initialBalanceGroup.style.display = 'block';
    }
    
    if (customPasswordCheck && customPasswordCheck.checked) {
        passwordGroup.style.display = 'block';
        passwordInput.required = true;
    }
    
    // Validazione form prima dell'invio
    document.getElementById('createUserForm').addEventListener('submit', function(e) {
        let hasErrors = false;
        
        // Verifica validità email
        const email = emailInput.value.trim();
        if (!validateEmail(email)) {
            e.preventDefault();
            const errorMessage = getEmailErrorMessage(email);
            showEmailError(errorMessage);
            hasErrors = true;
        }
        
        // Verifica validità telefono se compilato
        const phone = phoneInput.value.trim();
        if (phone !== '') {
            if (phone.length !== 10 || !/^\d{10}$/.test(phone)) {
                e.preventDefault();
                alert('Il numero di telefono deve essere di esattamente 10 cifre o lascia il campo vuoto.');
                phoneInput.focus();
                return false;
            }
        }
        
        if (hasErrors) {
            e.stopPropagation();
            
            // Focus sul primo campo con errore
            if (!validateEmail(email)) {
                emailInput.focus();
            }
            
            // Scroll verso il primo campo con errore
            const firstErrorField = document.querySelector('.is-invalid');
            if (firstErrorField) {
                firstErrorField.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
            
            return false;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        
        // Disabilita il pulsante per evitare doppi invii
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creazione in corso...';
        
        // Se si verifica un errore di validazione, riabilita il pulsante
        setTimeout(function() {
            if (document.querySelector('.alert-danger')) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Crea Utente';
            }
        }, 1000);
        
        return true;
    });
});
</script>
@endsection