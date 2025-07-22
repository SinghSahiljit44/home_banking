@extends('layouts.bootstrap')

@section('title', 'Sessione Scaduta')

@section('content')
<div class="container">
    <div class="row justify-content-center" style="margin-top: 100px;">
        <div class="col-md-6 text-center">
            <div class="card bg-transparent border-light">
                <div class="card-body">
                    <i class="fas fa-clock fa-5x text-warning mb-4"></i>
                    <h1 class="display-4 text-warning">419</h1>
                    <h3 class="mb-3">Sessione Scaduta</h3>
                    <p class="text-white-50 mb-4">La tua sessione è scaduta per motivi di sicurezza. Effettua nuovamente il login per continuare.</p>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Vai al Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Prevenzione del back button
history.pushState(null, null, location.href);
window.onpopstate = function () {
    window.location.href = "{{ route('login') }}";
};
</script>
@endsection