@extends('layouts.app')

@section('title', 'Registrazione')

@section('content')
<div class="container">
    <div class="row justify-content-center" style="margin-top: 80px;">
        <div class="col-md-6">
            <div class="card bg-transparent border-light">
                <div class="card-header text-center">
                    <h3 class="text-white">Registrazione Nuovo Cliente</h3>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" required maxlength="50">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Cognome</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" required maxlength="50">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required maxlength="50">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required maxlength="100">
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Telefono</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" maxlength="20">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Indirizzo</label>
                            <textarea class="form-control" id="address" name="address" rows="2">{{ old('address') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Conferma Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>

                        @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label text-white" for="terms">
                                    Accetto i <a href="{{ route('terms.show') }}" target="_blank" class="text-info">Termini di Servizio</a> 
                                    e la <a href="{{ route('policy.show') }}" target="_blank" class="text-info">Privacy Policy</a>
                                </label>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-success w-100">Registrati</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="text-white-50">Hai già un account? Accedi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection