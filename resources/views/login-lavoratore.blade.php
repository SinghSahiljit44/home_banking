@extends('layouts.app')

@section('title', 'Login Lavoratore')

@section('content')
<div class="login-box">
  <h3 class="text-center mb-4">Login Lavoratore</h3>
  <form method="POST" action="{{ route('lavoratore.login.submit') }}">
    @csrf
    <div class="mb-3">
      <label for="matricola" class="form-label">ID Dipendente</label>
      <input type="text" class="form-control" id="matricola" name="matricola" required autofocus>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Accedi</button>
  </form>
</div>
@endsection
