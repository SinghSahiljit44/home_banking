@extends('layouts.bootstrap')

@section('title', 'Verifica Domanda di Sicurezza')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-shield-alt me-2"></i>Verifica Identità</h4>
                        <a href="{{ route('client.security.questions') }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Indietro
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

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
                        <strong>Verifica di sicurezza richiesta.</strong><br>
                        Per procedere, rispondi alla tua domanda di sicurezza.
                    </div>

                    <form method="POST" action="{{ route('client.security.verify.check') }}">
                        @csrf
                        
                        <div class="card bg-dark border-secondary mb-4">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">La tua domanda di sicurezza:</label>
                                    <p class="h6 text-info">{{ $question }}</p>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="answer" class="form-label">La tua risposta *</label>
                                    <input type="text" 
                                           class="form-control @error('answer') is-invalid @enderror" 
                                           id="answer" 
                                           name="answer" 
                                           required 
                                           autofocus
                                           placeholder="Inserisci la tua risposta">
                                    @error('answer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">La risposta deve corrispondere esattamente a quella configurata</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check me-2"></i>Verifica Risposta
                            </button>
                            <a href="{{ route('client.security.questions') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annulla
                            </a>
                        </div>
                    </form>

                    <div class="mt-4">
                        <div class="card bg-dark border-warning">
                            <div class="card-body">
                                <h6 class="card-title text-warning">
                                    <i class="fas fa-question-circle me-2"></i>Non ricordi la risposta?
                                </h6>
                                <p class="mb-0 small">
                                    Se non ricordi la risposta alla domanda di sicurezza, contatta il nostro servizio clienti per assistenza nel recupero dell'accesso.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Focus automatico sul campo risposta
    const answerField = document.getElementById('answer');
    if (answerField) {
        answerField.focus();
    }
    
    // Validazione in tempo reale
    answerField.addEventListener('input', function() {
        const submitBtn = document.querySelector('button[type="submit"]');
        if (this.value.trim().length >= 3) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-success');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-success');
            submitBtn.classList.add('btn-secondary');
        }
    });
});
</script>
@endsection