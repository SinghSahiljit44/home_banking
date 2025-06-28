@extends('layouts.bootstrap')

@section('title', 'Cambia Password')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-key me-2"></i>Cambia Password</h4>
                        <a href="{{ route('client.profile.show') }}" class="btn btn-outline-light btn-sm">
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

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Per la tua sicurezza:</strong> Dopo aver inserito la nuova password, il cambio sarà effettuato immediatamente.
                    </div>

                    <form method="POST" action="{{ route('client.profile.change-password.store') }}" id="passwordForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Attuale *</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye" id="current_password_icon"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nuova Password *</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('new_password') is-invalid @enderror" 
                                       id="new_password" 
                                       name="new_password" 
                                       required 
                                       minlength="8">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye" id="new_password_icon"></i>
                                </button>
                            </div>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimo 8 caratteri</div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Conferma Nuova Password *</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="new_password_confirmation" 
                                       name="new_password_confirmation" 
                                       required 
                                       minlength="8">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')">
                                    <i class="fas fa-eye" id="new_password_confirmation_icon"></i>
                                </button>
                            </div>
                            <div class="form-text">Ripeti la nuova password</div>
                        </div>

                        <!-- Indicatore forza password -->
                        <div class="mb-3">
                            <div class="password-strength">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="strengthBar" style="width: 0%"></div>
                                </div>
                                <small id="strengthText" class="text-muted">Inserisci una password</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="card bg-dark border-secondary">
                                <div class="card-body py-2">
                                    <h6 class="card-title mb-2">Requisiti Password:</h6>
                                    <ul class="mb-0 small">
                                        <li id="length" class="text-muted">Almeno 8 caratteri</li>
                                        <li id="uppercase" class="text-muted">Almeno una lettera maiuscola</li>
                                        <li id="lowercase" class="text-muted">Almeno una lettera minuscola</li>
                                        <li id="number" class="text-muted">Almeno un numero</li>
                                        <li id="special" class="text-muted">Almeno un carattere speciale</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg" id="submitBtn" disabled>
                                <i class="fas fa-key me-2"></i>Cambia Password
                            </button>
                            <a href="{{ route('client.profile.show') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annulla
                            </a>
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
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('new_password_confirmation');
    const submitBtn = document.getElementById('submitBtn');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    // Elementi requisiti
    const requirements = {
        length: document.getElementById('length'),
        uppercase: document.getElementById('uppercase'),
        lowercase: document.getElementById('lowercase'),
        number: document.getElementById('number'),
        special: document.getElementById('special')
    };
    
    function checkPasswordStrength(password) {
        let score = 0;
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };
        
        // Aggiorna indicatori visivi
        Object.keys(checks).forEach(key => {
            if (checks[key]) {
                requirements[key].classList.remove('text-muted');
                requirements[key].classList.add('text-success');
                score++;
            } else {
                requirements[key].classList.remove('text-success');
                requirements[key].classList.add('text-muted');
            }
        });
        
        // Aggiorna barra progresso
        const percentage = (score / 5) * 100;
        strengthBar.style.width = percentage + '%';
        
        if (score < 2) {
            strengthBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Password debole';
            strengthText.className = 'text-danger';
        } else if (score < 4) {
            strengthBar.className = 'progress-bar bg-warning';
            strengthText.textContent = 'Password media';
            strengthText.className = 'text-warning';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Password forte';
            strengthText.className = 'text-success';
        }
        
        return score >= 3; // Minimo 3 requisiti per essere valida
    }
    
    function validateForm() {
        const currentPass = document.getElementById('current_password').value;
        const newPass = newPassword.value;
        const confirmPass = confirmPassword.value;
        
        const isValid = currentPass && 
                       newPass && 
                       confirmPass && 
                       newPass === confirmPass && 
                       checkPasswordStrength(newPass);
        
        submitBtn.disabled = !isValid;
        
        // Feedback per conferma password
        if (confirmPass && newPass !== confirmPass) {
            confirmPassword.classList.add('is-invalid');
        } else {
            confirmPassword.classList.remove('is-invalid');
        }
    }
    
    newPassword.addEventListener('input', function() {
        checkPasswordStrength(this.value);
        validateForm();
    });
    
    confirmPassword.addEventListener('input', validateForm);
    document.getElementById('current_password').addEventListener('input', validateForm);
});
</script>
@endsection