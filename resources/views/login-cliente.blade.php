@extends('layouts.app')

@section('title', 'Login Cliente')

@section('content')
<div class="login-box">
  <h3 class="text-center mb-4">Login Cliente</h3>
  <form method="POST" action="{{ route('cliente.login.submit') }}">
    @csrf
    <div class="mb-3">
      <label for="username" class="form-label">Codice Cliente</label>
      <input type="text" class="form-control" id="username" name="username" required autofocus>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-success w-100">Accedi</button>
  </form>
</div>
@endsection
