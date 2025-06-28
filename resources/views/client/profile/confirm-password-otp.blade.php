@extends('layouts.bootstrap')

@section('title', 'Cambia Password')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="text-white"><i class="fas fa-key me-2"></i>Cambia Password</h4>
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
                                    <li class="text-white">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong class="text-dark">Sicurezza:</strong> 
                        <span class="text-dark">La password verrà cambiata immediatamente dopo la conferma.</span>
                    </div>

                    <form method="POST" action="{{ route('client.profile.change-password.store') }}" id="passwordForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label text-white">Password Attuale *</label>
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
                            <label for="new_password" class="form-label text-white">Nuova Password *</label>
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
                            <div class="form-text text-white">Minimo 8 caratteri</div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label text-white">Conferma Nuova Password *</label>
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
                            <div class="form-text text-white">Ripeti la nuova password</div>
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
                                    <h6 class="card-title mb-2 text-white">Requisiti Password:</h6>
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

                        <!-- Conferma diretta -->
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong class="text-dark">Attenzione:</strong> 
                            <span class="text-dark">La password verrà cambiata immediatamente. Assicurati di ricordare la nuova password.</span>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg" id="submitBtn" disabled>
                                <i class="fas fa-key me-2"></i>Cambia Password Ora
                            </button>
                            <a href="{{ route('client.profile.show') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annullay