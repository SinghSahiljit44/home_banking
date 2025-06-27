@extends('layouts.bootstrap')

@section('title', 'Conferma Bonifico - OTP')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bg-transparent border-light">
                <div class="card-header text-center">
                    <h4><i class="fas fa-shield-alt me-2"></i>Conferma con OTP</h4>
                    <p class="mb-0 text-muted">Inserisci il codice di sicurezza per completare il bonifico</p>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <!-- Riepilogo bonifico -->
                    <div class="card bg-dark border-secondary mb-4">
                        <div class="card-body">
                            <h6 class="card-title text-info">Riepilogo Bonifico</h6>
                            <div class="row">
                                <div class="col-12">
                                    <p class="mb-1"><strong>Beneficiario:</strong> {{ $transfer_data['beneficiary_name'] ?? 'Non specificato' }}</p>
                                    <p class="mb-1"><strong>IBAN:</strong> {{ $transfer_data['recipient_iban'] }}</p>
                                    <p class="mb-1"><strong>Importo:</strong> 
                                        <span class="text-warning">€{{ number_format($transfer_data['amount'], 2, ',', '.') }}</span>
                                    </p>
                                    <p class="mb-0"><strong>Causale:</strong> {{ $transfer_data['description'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form OTP -->
                    <form method="POST" action="{{ route('client.transfer.confirm') }}" id="otpForm">
                        @csrf
                        
                        <div class="text-center mb-4">
                            <div class="alert alert-info">
                                <i class="fas fa-sms me-2"></i>
                                Abbiamo inviato un codice di sicurezza al tuo numero di telefono registrato.
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
                                <i class="fas fa-check me-2"></i>Conferma Bonifico
                            </button>
                            <a href="{{ route('client.transfer.cancel') }}" class="btn btn-outline-danger">
                                <i class="fas fa-times me-2"></i>Annulla Operazione
                            </a>
                        </div>
                    </form>

                    <!-- Link per richiedere nuovo OTP -->
                    <div class="text-center mt-3">
                        <a href="#" id="resendOtp" class="text-info" style="display: none;">
                            <i class="fas fa-redo me-1"></i>Richiedi nuovo codice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otp');
    const timerElement = document.getElementById('timer');
    const resendLink = document.getElementById('resendOtp');
    const confirmBtn = document.getElementById('confirmBtn');
    
    // Timer per scadenza OTP (5 minuti)
    let timeLeft = 300; // 5 minuti in secondi
    
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
            resendLink.style.display = 'inline';
        } else if (timeLeft <= 60) {
            timerElement.className = 'text-danger';
        }
        
        timeLeft--;
    }, 1000);
    
    // Formatta input OTP (solo numeri)
    otpInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
        
        // Auto-submit quando raggiunge 6 cifre
        if (e.target.value.length === 6) {
            setTimeout(() => {
                document.getElementById('otpForm').submit();
            }, 500);
        }
    });
    
    // Gestione tasti per OTP
    otpInput.addEventListener('keydown', function(e) {
        // Permetti solo numeri, backspace, delete, tab, frecce
        if (![8, 9, 27, 46, 37, 38, 39, 40].includes(e.keyCode) && 
            (e.keyCode < 48 || e.keyCode > 57) && 
            (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    
    // Focus automatico su input
    otpInput.focus();
    
    // Richiesta nuovo OTP
    resendLink.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Vuoi richiedere un nuovo codice OTP?')) {
            window.location.href = '{{ route("client.transfer.cancel") }}';
        }
    });
});
</script>
@endsection