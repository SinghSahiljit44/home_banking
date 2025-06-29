@extends('layouts.bootstrap')

@section('title', 'Dashboard Cliente')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-user me-2"></i>La Mia Banca Online</h2>
                    <p class="text-muted">Benvenuto, {{ Auth::user()->full_name }}</p>
                </div>
                <div>
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>{{ Auth::user()->full_name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="{{ route('client.profile.show') }}">
                            <i class="fas fa-user me-2"></i>Il Mio Profilo
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('client.security.questions') }}">
                            <i class="fas fa-shield-alt me-2"></i>Sicurezza
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (!Auth::user()->account)
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Attenzione:</strong> Non hai ancora un conto corrente associato. Contatta la tua filiale per aprirne uno.
        </div>
    @endif

    <!-- Riepilogo Conto -->
    @if(Auth::user()->account)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-transparent border-success">
                    <div class="card-header bg-success">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Il Tuo Conto Corrente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Saldo Disponibile</h6>
                                <h3 class="text-success">€{{ number_format(Auth::user()->account->balance, 2, ',', '.') }}</h3>
                            </div>
                            <div class="col-md-4">
                                <h6>IBAN</h6>
                                <p class="font-monospace">{{ Auth::user()->account->iban }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6>Stato Conto</h6>
                                <span class="badge {{ Auth::user()->account->is_active ? 'bg-success' : 'bg-danger' }} fs-6">
                                    {{ Auth::user()->account->is_active ? 'Attivo' : 'Sospeso' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Menu Servizi -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-transparent border-primary h-100">
                <div class="card-body text-center">
                    <i class="fas fa-paper-plane fa-3x text-primary mb-3"></i>
                    <h5>Bonifico</h5>
                    <p class="small text-muted">Invia denaro a un altro conto</p>
                    @if(Auth::user()->account && Auth::user()->account->is_active)
                        <a href="{{ route('client.transfer.create') }}" class="btn btn-primary">
                            Esegui
                        </a>
                    @else
                        <button class="btn btn-secondary" disabled>Non Disponibile</button>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card bg-transparent border-info h-100">
                <div class="card-body text-center">
                    <i class="fas fa-list fa-3x text-info mb-3"></i>
                    <h5>Estratto Conto</h5>
                    <p class="small text-muted">Visualizza movimenti e saldo</p>
                    @if(Auth::user()->account)
                        <a href="{{ route('client.account.show') }}" class="btn btn-info">
                            Visualizza
                        </a>
                    @else
                        <button class="btn btn-secondary" disabled>Non Disponibile</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Movimenti Recenti -->
    @if(Auth::user()->account)
        <div class="row">
            <div class="col-md-8">
                <div class="card bg-transparent border-light">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-history me-2"></i>Movimenti Recenti</h5>
                            <a href="{{ route('client.account.show') }}" class="btn btn-sm btn-outline-light">
                                Vedi Tutti
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $recentTransactions = Auth::user()->account->allTransactions()->take(5)->get();
                        @endphp
                        
                        @forelse($recentTransactions as $transaction)
                            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary py-2">
                                <div>
                                    <div class="fw-bold">
                                        {{ Str::limit($transaction->description, 30) }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    @if($transaction->from_account_id === Auth::user()->account->id)
                                        <span class="text-danger fw-bold">
                                            -€{{ number_format($transaction->amount, 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-success fw-bold">
                                            +€{{ number_format($transaction->amount, 2, ',', '.') }}
                                        </span>
                                    @endif
                                    <br>
                                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nessuna transazione recente</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-transparent border-light">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle me-2"></i>Informazioni Utili</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item bg-transparent border-secondary">
                                <strong>Orari Servizio:</strong><br>
                                <small class="text-muted">Lun-Ven: 8:00-20:00<br>Sab: 8:00-13:00</small>
                            </div>
                            <div class="list-group-item bg-transparent border-secondary">
                                <strong>Assistenza:</strong><br>
                                <small class="text-muted">800-123-456 (gratuito)</small>
                            </div>
                            <div class="list-group-item bg-transparent border-secondary">
                                <strong>Sicurezza:</strong><br>
                                <small class="text-muted">
                                    <a href="{{ route('client.security.questions') }}" class="text-decoration-none">
                                        Configura domande di sicurezza
                                    </a>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Azioni Rapide -->
                <div class="card bg-transparent border-light mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-zap me-2"></i>Azioni Rapide</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if(Auth::user()->account && Auth::user()->account->is_active)
                                <a href="{{ route('client.transfer.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-paper-plane me-2"></i>Nuovo Bonifico
                                </a>
                            @endif
                            <a href="{{ route('client.account.show') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-download me-2"></i>Scarica Estratto
                            </a>
                            <a href="{{ route('client.profile.show') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-user me-2"></i>Modifica Profilo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Messaggio per clienti senza conto -->
        <div class="row">
            <div class="col-12">
                <div class="card bg-transparent border-info">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-university fa-4x text-info mb-4"></i>
                        <h4>Benvenuto nel nostro sistema bancario!</h4>
                        <p class="text-muted">Per iniziare ad utilizzare i nostri servizi, è necessario aprire un conto corrente.</p>
                        <p class="text-muted">Contatta la tua filiale di riferimento o un nostro consulente per procedere con l'apertura.</p>
                        <div class="mt-4">
                            <div class="row justify-content-center">
                                <div class="col-md-4">
                                    <div class="card bg-dark border-secondary">
                                        <div class="card-body text-center">
                                            <i class="fas fa-phone fa-2x text-info mb-2"></i>
                                            <h6>Contattaci</h6>
                                            <p class="small">800-123-456</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
