@extends('layouts.bootstrap')

@section('title', 'Modifica Cliente')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-edit me-2"></i>Modifica Cliente: {{ $client->full_name }}</h4>
                        <a href="{{ route('employee.clients.show', $client) }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Torna ai Dettagli
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

                    <form method="POST" action="{{ route('employee.clients.update', $client) }}">
                        @csrf
                        @method('PUT')
                        
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
                                               value="{{ old('first_name', $client->first_name) }}" 
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
                                               value="{{ old('last_name', $client->last_name) }}" 
                                               required 
                                               maxlength="50">
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" 
                                            class="form-control @error('email') is-invalid @enderror" 
                                            id="email" 
                                            name="email" 
                                            value="{{ old('email', $client->email) }}" 
                                            required 
                                            maxlength="100"
                                            minlength="5">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback" id="email-custom-error" style="display: none;">
                                            Inserisci un indirizzo email valido con dominio completo (es. nome@dominio.com)
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Telefono</label>
                                        <input type="tel" 
                                            class="form-control @error('phone') is-invalid @enderror" 
                                            id="phone" 
                                            name="phone" 
                                            value="{{ old('phone', $client->phone) }}" 
                                            maxlength="10"
                                            placeholder="3123456789">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="invalid-feedback" id="phone-custom-error" style="display: none;">
                                            Il numero di telefono deve essere di esattamente 10 cifre
                                        </div>
                                        <small class="form-text text-muted">Inserisci 10 cifre senza spazi o caratteri speciali</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Indirizzo</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3" 
                                              maxlength="500">{{ old('address', $client->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Informazioni di Sola Lettura -->
                        <div class="card bg-info bg-opacity-10 border-info mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-info-circle me-2"></i>Informazioni di Sistema</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Username:</strong> {{ $client->username }}</p>
                                        <p><strong>Ruolo:</strong> {{ ucfirst($client->role) }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Registrato:</strong> {{ $client->created_at->format('d/m/Y H:i') }}</p>
                                        <p><strong>Ultimo Aggiornamento:</strong> {{ $client->updated_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Username e ruolo non possono essere modificati. Per il reset password usa l'apposita funzione.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('employee.clients.show', $client) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annulla
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Salva Modifiche
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

    // Funzione di validazione telefono
    function validatePhone(phone) {
        const trimmedPhone = phone.trim();
        
        // Se vuoto, è opzionale quindi valido
        if (!trimmedPhone) return true;
        
        // Deve contenere esattamente 10 cifre e solo cifre
        const phoneRegex = /^[0-9]{10}$/;
        return phoneRegex.test(trimmedPhone);
    }

    // Funzioni per gestire errori email
    function showEmailError(message) {
        const emailField = document.getElementById('email');
        const errorDiv = document.getElementById('email-custom-error');
        
        emailField.setCustomValidity(message);
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        emailField.classList.add('is-invalid');
    }

    function clearEmailError() {
        const emailField = document.getElementById('email');
        const errorDiv = document.getElementById('email-custom-error');
        
        emailField.setCustomValidity('');
        errorDiv.style.display = 'none';
        emailField.classList.remove('is-invalid');
    }

    // Funzioni per gestire errori telefono
    function showPhoneError(message) {
        const phoneField = document.getElementById('phone');
        const errorDiv = document.getElementById('phone-custom-error');
        
        phoneField.setCustomValidity(message);
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        phoneField.classList.add('is-invalid');
    }

    function clearPhoneError() {
        const phoneField = document.getElementById('phone');
        const errorDiv = document.getElementById('phone-custom-error');
        
        phoneField.setCustomValidity('');
        errorDiv.style.display = 'none';
        phoneField.classList.remove('is-invalid');
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

    // Funzione per determinare il messaggio di errore telefono
    function getPhoneErrorMessage(phone) {
        const trimmedPhone = phone.trim();
        
        if (trimmedPhone.length === 0) {
            return ''; // Campo opzionale
        }
        
        // Controlla se contiene caratteri non numerici
        if (!/^[0-9]*$/.test(trimmedPhone)) {
            return 'Il numero deve contenere solo cifre (0-9)';
        }
        
        if (trimmedPhone.length < 10) {
            return `Il numero deve essere di 10 cifre (inserite: ${trimmedPhone.length})`;
        }
        
        if (trimmedPhone.length > 10) {
            return `Il numero deve essere di 10 cifre (inserite: ${trimmedPhone.length})`;
        }
        
        return 'Il numero di telefono deve essere di esattamente 10 cifre';
    }

    // Funzione di validazione email
    function validateEmailField() {
        const emailField = document.getElementById('email');
        const email = emailField.value;
        
        if (!email) {
            clearEmailError();
            return;
        }

        if (!validateEmail(email)) {
            const errorMessage = getEmailErrorMessage(email);
            showEmailError(errorMessage);
        } else {
            clearEmailError();
        }
    }

    // Funzione di validazione telefono
    function validatePhoneField() {
        const phoneField = document.getElementById('phone');
        const phone = phoneField.value;
        
        if (!validatePhone(phone)) {
            const errorMessage = getPhoneErrorMessage(phone);
            showPhoneError(errorMessage);
        } else {
            clearPhoneError();
        }
    }

    // Event listeners per email
    const emailField = document.getElementById('email');
    emailField.addEventListener('input', validateEmailField);
    emailField.addEventListener('blur', validateEmailField);

    // Event listeners per telefono
    const phoneField = document.getElementById('phone');
    
    // Consenti solo numeri durante la digitazione
    phoneField.addEventListener('input', function(e) {
        // Rimuovi tutti i caratteri non numerici
        const value = e.target.value.replace(/[^0-9]/g, '');
        
        // Limita a 10 caratteri
        e.target.value = value.substring(0, 10);
        
        // Valida dopo aver pulito l'input
        validatePhoneField();
    });

    phoneField.addEventListener('blur', validatePhoneField);

    // Previeni l'inserimento di caratteri non numerici
    phoneField.addEventListener('keypress', function(e) {
        // Consenti solo numeri, backspace, delete, tab, escape, enter
        const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter'];
        const isNumberKey = (e.key >= '0' && e.key <= '9');
        
        if (!isNumberKey && !allowedKeys.includes(e.key)) {
            e.preventDefault();
        }
    });

    // Validazione prima del submit
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;
        
        let hasErrors = false;
        
        // Valida email
        if (!validateEmail(email)) {
            e.preventDefault();
            const errorMessage = getEmailErrorMessage(email);
            showEmailError(errorMessage);
            hasErrors = true;
        }
        
        // Valida telefono
        if (!validatePhone(phone)) {
            e.preventDefault();
            const errorMessage = getPhoneErrorMessage(phone);
            showPhoneError(errorMessage);
            hasErrors = true;
        }
        
        if (hasErrors) {
            e.stopPropagation();
            
            // Focus sul primo campo con errore
            if (!validateEmail(email)) {
                document.getElementById('email').focus();
            } else if (!validatePhone(phone)) {
                document.getElementById('phone').focus();
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
    });
});
</script>

@endsection