@extends('layouts.bootstrap')

@section('title', 'Dashboard Cliente')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Benvenuto, {{ Auth::user()->full_name }}</h2>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light">Logout</button>
                </form>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5 class="text-white">Il tuo Conto</h5>
                </div>
                <div class="card-body">
                    @if(Auth::user()->account)
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><span class="text-white">Numero Conto:</span></strong><br><span class="text-white">{{ Auth::user()->account->account_number }}</span></p>
                                <p><strong><span class="text-white">IBAN:</span></strong><br><span class="text-white">{{ Auth::user()->account->iban }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><span class="text-white">Saldo Disponibile:</span></strong></p>
                                <h3 class="text-success">€{{ number_format(Auth::user()->account->balance, 2, ',', '.') }}</h3>
                                <p><strong><span class="text-white">Stato Conto:</span></strong> 
                                    <span class="badge {{ Auth::user()->account->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ Auth::user()->account->is_active ? 'Attivo' : 'Sospeso' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Nessun conto associato al tuo profilo. Contatta l'assistenza clienti per aprire un conto.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5 class="text-white">Azioni Rapide</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(Auth::user()->account && Auth::user()->account->is_active)
                            <a href="{{ route('client.transfer.create') }}" class="btn btn-success">
                                <i class="fas fa-exchange-alt me-2"></i>Bonifico
                            </a>
                        @else
                            <button class="btn btn-success" disabled>
                                <i class="fas fa-exchange-alt me-2"></i>Bonifico
                            </button>
                        @endif
                        
                        <a href="{{ route('client.account.show') }}" class="btn btn-info">
                            <i class="fas fa-file-alt me-2"></i>Estratto Conto
                        </a>
                        
                        <a href="{{ route('client.profile.show') }}" class="btn btn-warning">
                            <i class="fas fa-user me-2"></i>Il mio Profilo
                        </a>
                    </div>
                    @if(!Auth::user()->account || !Auth::user()->account->is_active)
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle me-1"></i>
                            Alcune funzioni richiedono un conto attivo
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5 class="text-white">Ultime Transazioni</h5>
                </div>
                <div class="card-body">
                    @if(Auth::user()->account && Auth::user()->account->allTransactions()->exists())
                        <div class="table-responsive">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Descrizione</th>
                                        <th>Importo</th>
                                        <th>Stato</th>
                                        <th>Riferimento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(Auth::user()->account->allTransactions()->take(10)->get() as $transaction)
                                    @php
                                        $account = Auth::user()->account;
                                        $isIncoming = $account->isIncomingTransaction($transaction);
                                        $isOutgoing = $account->isOutgoingTransaction($transaction);
                                        $amount = $account->getTransactionAmount($transaction);
                                        $description = $account->getTransactionDescription($transaction);
                                    @endphp
                                    <tr>
                                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $description }}</td>
                                        <td>
                                            @if($isOutgoing)
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down me-1"></i>
                                                    €{{ number_format($amount, 2, ',', '.') }}
                                                </span>
                                            @elseif($isIncoming)
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up me-1"></i>
                                                    +€{{ number_format($amount, 2, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    €{{ number_format($transaction->amount, 2, ',', '.') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted, text-white">{{ $transaction->reference_code }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(Auth::user()->account->allTransactions()->count() > 10)
                            <div class="text-center mt-3">
                                <a href="{{ route('client.account.show') }}" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-eye me-1"></i>Vedi tutte le transazioni
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nessuna transazione trovata.</p>
                            @if(Auth::user()->account)
                                <p class="text-white-50">Le tue transazioni appariranno qui non appena effettuerai operazioni sul conto.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Informazioni aggiuntive -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5 class="text-white">Informazioni Profilo</h5>
                </div>
                <div class="card-body">
                    <p class="text-white"><strong>Nome Completo:</strong> {{ Auth::user()->full_name }}</p>
                    <p class="text-white"><strong>Email:</strong> {{ Auth::user()->email }}</p>
                    @if(Auth::user()->phone)
                        <p class="text-white"><strong>Telefono:</strong> {{ Auth::user()->phone }}</p>
                    @endif
                    @if(Auth::user()->address)
                        <p class="text-white"><strong>Indirizzo:</strong> {{ Auth::user()->address }}</p>
                    @endif
                    <small class="text-white">Registrato il {{ Auth::user()->created_at->format('d/m/Y') }}</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5 class="text-white">Accesso Rapido</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush bg-transparent">
                        <a href="#" class="list-group-item list-group-item-action bg-transparent text-white border-secondary disabled">
                            <i class="fas fa-phone me-2"></i>Contatta l'Assistenza
                        </a>
                        <a href="#" class="list-group-item list-group-item-action bg-transparent text-white border-secondary disabled">
                            <i class="fas fa-map-marker-alt me-2"></i>Trova Filiale
                        </a>
                        <a href="#" class="list-group-item list-group-item-action bg-transparent text-white border-secondary disabled">
                            <i class="fas fa-download me-2"></i>App Mobile
                        </a>
                        <a href="#" class="list-group-item list-group-item-action bg-transparent text-white border-secondary disabled">
                            <i class="fas fa-shield-alt me-2"></i>Sicurezza
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection