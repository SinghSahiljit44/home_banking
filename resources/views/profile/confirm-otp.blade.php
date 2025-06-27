@extends('layouts.bootstrap')

@section('title', 'Conferma Modifiche - OTP')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bg-transparent border-light">
                <div class="card-header text-center">
                    <h4><i class="fas fa-shield-alt me-2"></i>Conferma con OTP</h4>
                    <p class="mb-0 text-muted">Inserisci il codice di sicurezza per confermare le modifiche</p>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <!-- Riepilogo modifiche -->
                    <div class="card bg-dark border-secondary mb-4">
                        <div class="card-body">
                            <h6 class="card-title text-info">Modifiche da Applicare</h6>
                            @foreach($changes as $field => $value)
                                <p class="mb-1">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}:</strong> 
                                    {{ $value }}
                                </p>
                            @endforeach
                        </div>
                    </div>

                    <!-- Form OTP -->
                    <form method="POST" action="{{ route('client.profile.confirm-changes') }}" id="otpForm">
                        @csrf
                        
                        <div class="text-center mb-4">
                            <div class="alert alert-info">
                                <i class="fas fa-sms me-2"></i>
                                Abbiamo inviato un codice di sicurezza al tuo numero di telefono.
                                @if($development_otp)
                                    <br><strong class="text-warning">SVILUPPO - OTP: {{ $development_otp }}</strong>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="otp" class="form-label text-center d-block">Codice OTP</label>
                            <input type="text" 
                                   class="form-control form-control-lg text-center @error('otp') is-invalid @enderror" 
                                   id="otp" 
                                   name="otp" 
                                   maxlength="6" 
                                   placeholder="000000"
                                   autocomplete="off"
                                   required
                                   autofocus>
                            @error('otp')
                                <div class="invalid-feedback text-center">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-center">Inserisci il codice di 6 cifre</div>
                        </div>

                        <!-- Timer -->
                        <div class="text-center mb-4">
                            <small class="text-muted">
                                Il codice scadrà tra: <span id="timer" class="text-warning">05:00</span>
                            </small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="confirmBtn">
                                <i class="fas fa-check me-2"></i>Conferma Modifiche
                            </button>
                            <a href="{{ route('client.profile.cancel-changes') }}" class="btn btn-outline-danger">
                                <i class="fas fa-times me-2"></i>Annulla Modifiche
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otp');
    const timerElement = document.getElementById('timer');
    const confirmBtn = document.getElementById('confirmBtn');
    
    // Timer per scadenza OTP (5 minuti)
    let timeLeft = 300;
    
    const countdown = setInterval(function() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        timerElement.textContent = 
            String(minutes).padStart(2, '0') + ':' + 
            String(seconds).padStart(2, '0');
        
        if (timeLeft <= 0) {
            clearInterval(countdown);
            timerElement.textContent = '00:00';
            timerElement.className = 'text-danger';
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-clock me-2"></i>Codice Scaduto';
        } else if (timeLeft <= 60) {
            timerElement.className = 'text-danger';
        }
        
        timeLeft--;
    }, 1000);
    
    // Formatta input OTP
    otpInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
        
        if (e.target.value.length === 6) {
            setTimeout(() => {
                document.getElementById('otpForm').submit();
            }, 500);
        }
    });
    
    otpInput.addEventListener('keydown', function(e) {
        if (![8, 9, 27, 46, 37, 38, 39, 40].includes(e.keyCode) && 
            (e.keyCode < 48 || e.keyCode > 57) && 
            (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    
    otpInput.focus();
});
</script>
@endsection