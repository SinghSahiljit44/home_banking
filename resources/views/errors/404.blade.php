@extends('layouts.bootstrap')

@section('title', 'Pagina Non Trovata')

@section('content')
<div class="container">
    <div class="row justify-content-center" style="margin-top: 100px;">
        <div class="col-md-6 text-center">
            <div class="card bg-transparent border-light">
                <div class="card-body">
                    <i class="fas fa-search fa-5x text-warning mb-4"></i>
                    <h1 class="display-4 text-warning">404</h1>
                    <h3 class="mb-3">Pagina Non Trovata</h3>
                    <p class="text-white-50 mb-4">La pagina che stai cercando non esiste o è stata spostata.</p>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ url()->previous() }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Torna Indietro
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-light">
                            <i class="fas fa-home me-2"></i>Vai alla Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection