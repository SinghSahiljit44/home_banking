@extends('layouts.bootstrap')

@section('title', 'Il mio Profilo')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user me-2"></i>Il mio Profilo</h2>
                <a href="{{ route('dashboard.cliente') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Torna alla Dashboard
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Informazioni Personali -->
        <div class="col-lg-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-id-card me-2"></i>Dati Personali</h5>
                        <a href="{{ route('client.profile.edit') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Modifica
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Nome</label>
                                <p class="h6">{{ $user->first_name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Cognome</label>
                                <p class="h6">{{ $user->last_name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Username</label>
                                <p class="h6">{{ $user->username }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Email</label>
                                <p class="h6">
                                    {{ $user->email }}
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success ms-2">Verificata</span>
                                    @else
                                        <span class="badge bg-warning ms-2">Non Verificata</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Telefono</label>
                                <p class="h6">{{ $user->phone ?: 'Non specificato' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Data Registrazione</label>
                                <p class="h6">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($user->address)
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Indirizzo</label>
                                    <p class="h6">{{ $user->address }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Azioni Sicurezza -->
        <div class="col-lg-4">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-shield-alt me-2"></i>Sicurezza</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('client.profile.change-password') }}" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Cambia Password
                        </a>
                        <button class="btn btn-info" disabled>
                            <i class="fas fa-question-circle me-2"></i>Domande Sicurezza
                        </button>
                        <button class="btn btn-secondary" disabled>
                            <i class="fas fa-mobile-alt me-2"></i>Autenticazione 2FA
                        </button>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <i class="fas fa-info-circle me-1"></i>
                        Alcune funzionalità sono in sviluppo
                    </small>
                </div>
            </div>

            <!-- Informazioni Account -->
            <div class="card bg-transparent border-light mt-3">
                <div class="card-header">
                    <h6><i class="fas fa-info-circle me-2"></i>Informazioni Account</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Stato Account:</strong> 
                        <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $user->is_active ? 'Attivo' : 'Sospeso' }}
                        </span>
                    </p>
                    <p class="mb-2">
                        <strong>Ruolo:</strong> 
                        <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                    </p>
                    @if($user->account)
                        <p class="mb-2">
                            <strong>Conto Associato:</strong> 
                            <span class="badge bg-success">Presente</span>
                        </p>
                        <p class="mb-0">
                            <strong>Numero Conto:</strong><br>
                            <small class="font-monospace">{{ $user->account->account_number }}</small>
                        </p>
                    @else
                        <p class="mb-0">
                            <strong>Conto Associato:</strong> 
                            <span class="badge bg-warning">Nessuno</span>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Attività Recente -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-history me-2"></i>Attività Recente</h5>
                </div>
                <div class="card-body">
                    @if($user->account && $user->account->allTransactions()->exists())
                        <div class="table-responsive">
                            <table class="table table-dark table-sm">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Tipo</th>
                                        <th>Descrizione</th>
                                        <th class="text-end">Importo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->account->allTransactions()->take(5)->get() as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($transaction->from_account_id === $user->account->id)
                                                <span class="badge bg-primary">Uscita</span>
                                            @else
                                                <span class="badge bg-success">Entrata</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($transaction->description, 30) }}</td>
                                        <td class="text-end">
                                            @if($transaction->from_account_id === $user->account->id)
                                                <span class="text-danger">-€{{ number_format($transaction->amount, 2, ',', '.') }}</span>
                                            @else
                                                <span class="text-success">+€{{ number_format($transaction->amount, 2, ',', '.') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('client.account.show') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-eye me-1"></i>Visualizza tutti i movimenti
                            </a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-history fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Nessuna attività recente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection