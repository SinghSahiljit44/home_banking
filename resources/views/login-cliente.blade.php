@extends('layouts.app')

@section('title', 'Login Cliente')

@section('content')
<div class="login-box">
  <h3 class="text-center mb-4">Login Cliente</h3>
  
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

  <form method="POST" action="{{ route('cliente.login.submit') }}">
    @csrf
    <div class="mb-3">
      <label for="username" class="form-label">Codice Cliente</label>
      <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required autofocus>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-success w-100">Accedi</button>
  </form>
  
  <div class="text-center mt-3">
    <a href="{{ url('/login') }}" class="text-white-50">← Torna alla selezione</a>
  </div>
</div>
@endsection