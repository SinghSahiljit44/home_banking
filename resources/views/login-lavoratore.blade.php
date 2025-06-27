@extends('layouts.bootstrap')

@section('title', 'Login Lavoratore')

@section('content')
<div class="login-box">
  <h3 class="text-center mb-4">Login Lavoratore</h3>
  
  @if ($errors->any())
    <div class="alert alert-danger">
      @foreach ($errors->all() as $error)
        <p class="mb-0">{{ $error }}</p>
      @endforeach
    </div>
  @endif

  @if (session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('lavoratore.login.submit') }}">
    @csrf
    <div class="mb-3">
      <label for="matricola" class="form-label">ID Dipendente</label>
      <input type="text" class="form-control" id="matricola" name="matricola" value="{{ old('matricola') }}" required autofocus>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <div class="mb-3 form-check">
      <input type="checkbox" class="form-check-input" id="remember" name="remember">
      <label class="form-check-label text-white" for="remember">
        Ricordami
      </label>
    </div>
    <button type="submit" class="btn btn-primary w-100">Accedi</button>
  </form>
  
  <div class="text-center mt-3">
    <a href="{{ url('/login') }}" class="text-white-50">← Torna alla selezione</a>
  </div>
</div>
@endsection