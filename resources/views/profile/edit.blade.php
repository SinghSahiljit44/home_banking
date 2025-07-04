@extends('layouts.bootstrap')

@section('title', 'Modifica Profilo')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-edit me-2"></i>Modifica Profilo</h4>
                        @if(Auth::user()->isClient())
                            <a href="{{ route('client.profile.show') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Torna al Profilo
                            </a>
                        @elseif(Auth::user()->isAdmin())
                            <a href="{{ route('admin.profile.show') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Torna al Profilo
                            </a>
                        @else
                            <a href="{{ route('employee.profile.show') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Torna al Profilo
                            </a>
                        @endif
                    </div>
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

                    <form method="POST" action="@if(Auth::user()->isClient()){{ route('client.profile.update') }}@elseif(Auth::user()->isAdmin()){{ route('admin.profile.update') }}@else{{ route('employee.profile.update') }}@endif">
                        @csrf
                        
                        <div class="card bg-dark border-secondary mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-id-card me-2"></i>Informazioni Personali</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">Nome *</label>
                                        <input type="text" 
                                               class="form-control @error('first_name') is-invalid @enderror" 
                                               id="first_name" 
                                               name="first_name" 
                                               value="{{ old('first_name', $user->first_name) }}" 
                                               required 
                                               maxlength="50">
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Cognome *</label>
                                        <input type="text" 
                                               class="form-control @error('last_name') is-invalid @enderror" 
                                               id="last_name" 
                                               name="last_name" 
                                               value="{{ old('last_name', $user->last_name) }}" 
                                               required 
                                               maxlength="50">
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email', $user->email) }}" 
                                               required 
                                               maxlength="100">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Telefono</label>
                                        <input type="tel" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone', $user->phone) }}" 
                                               maxlength="20">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Indirizzo</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3" 
                                              maxlength="500">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card bg-info bg-opacity-10 border-info mb-4">
                            <div class="card-header">
                                <h6><i class="fas fa-info-circle me-2"></i>Informazioni di Sistema</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Username:</strong> {{ $user->username }}</p>
                                        <p><strong>Ruolo:</strong> 
                                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'employee' ? 'warning' : 'success') }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Registrato:</strong> {{ $user->created_at->format('d/m/Y') }}</p>
                                        <p><strong>Ultimo Aggiornamento:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Username e ruolo non possono essere modificati.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            @if(Auth::user()->isClient())
                                <a href="{{ route('client.profile.show') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Annulla
                                </a>
                            @elseif(Auth::user()->isAdmin())
                                <a href="{{ route('admin.profile.show') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Annulla
                                </a>
                            @else
                                <a href="{{ route('employee.profile.show') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Annulla
                                </a>
                            @endif
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Salva Modifiche
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection