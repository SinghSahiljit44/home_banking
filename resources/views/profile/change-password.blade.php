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
                        @if(Auth::user()->isClient())
                            <a href="{{ route('client.profile.show') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Torna al Profilo
                            </a>
                        @elseif(Auth::user()->isAdmin())
                            <a href="{{ route('admin.profile.show') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Torna al Profilo
                            </a>
                        @else
                            <a href="{{ route('employee.profile.show') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Torna al Profilo
                            </a>
                        @endif
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
                        <strong>Sicurezza:</strong> Scegli una password sicura di almeno 8 caratteri.
                    </div>

                    <form method="POST" action="@if(Auth::user()->isClient()){{ route('client.profile.change-password.store') }}@elseif(Auth::user()->isAdmin()){{ route('admin.profile.change-password.store') }}@else{{ route('employee.profile.change-password.store') }}@endif" id="passwordForm">
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
                                       minlength="8"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye" id="new_password_icon"></i>
                                </button>
                            </div>
                            <div class="form-text">Minimo 8 caratteri</div>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="new_password_confirmation" class="form-label">Conferma Nuova Password *</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="new_password_confirmation" 
                                       name="new_password_confirmation" 
                                       minlength="8"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')">
                                    <i class="fas fa-eye" id="new_password_confirmation_icon"></i>
                                </button>
                            </div>
                            <div id="password-match" class="form-text"></div>
                        </div>

                        <div class="d-flex justify-content-between">
                            @if(Auth::user()->isClient())
                                <a href="{{ route('client.profile.show') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Annulla
                                </a>
                            @elseif(Auth::user()->isAdmin())
                                <a href="{{ route('admin.profile.show') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Annulla
                                </a>
                            @else
                                <a href="{{ route('employee.profile.show') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Annulla
                                </a>
                            @endif
                            <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                                <i class="fas fa-save me-1"></i>Cambia Password
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
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('new_password_confirmation');
    const passwordMatch = document.getElementById('password-match');
    const submitBtn = document.getElementById('submitBtn');
    
    function checkPasswordMatch() {
        const newPass = newPassword.value;
        const confirmPass = confirmPassword.value;
        
        if (newPass && confirmPass) {
            if (newPass === confirmPass) {
                passwordMatch.textContent = '✓ Le password corrispondono';
                passwordMatch.className = 'form-text text-success';
                submitBtn.disabled = false;
            } else {
                passwordMatch.textContent = '✗ Le password non corrispondono';
                passwordMatch.className = 'form-text text-danger';
                submitBtn.disabled = true;
            }
        } else {
            passwordMatch.textContent = '';
            submitBtn.disabled = true;
        }
    }
    
    newPassword.addEventListener('input', checkPasswordMatch);
    confirmPassword.addEventListener('input', checkPasswordMatch);
});
</script>
@endsection