@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container text-center" style="margin-top: 100px;">
  <h2 class="mb-5">Seleziona il tipo di accesso</h2>
  <div class="row justify-content-center g-4">
    <div class="col-md-4">
      <div class="choice-card">
        <h4>Accesso Cliente</h4>
        <p class="text-white-50">Controlla conti, carte e operazioni personali.</p>
        <a href="{{ url('/login-cliente') }}" class="btn btn-success w-100 mt-3">Login Cliente</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="choice-card">
        <h4>Accesso Lavoratore</h4>
        <p class="text-white-50">Accedi alla tua area come dipendente.</p>
        <a href="{{ url('/login-lavoratore') }}" class="btn btn-primary w-100 mt-3">Login Lavoratore</a>
      </div>
    </div>
  </div>
</div>
@endsection
