@extends('layouts.app')

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

    <div class="row">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5>Il tuo Conto</h5>
                </div>
                <div class="card-body">
                    @if(Auth::user()->account)
                        <p><strong>Numero Conto:</strong> {{ Auth::user()->account->account_number }}</p>
                        <p><strong>IBAN:</strong> {{ Auth::user()->account->iban }}</p>
                        <p><strong>Saldo:</strong> €{{ number_format(Auth::user()->account->balance, 2, ',', '.') }}</p>
                        <p><strong>Stato:</strong> 
                            <span class="badge {{ Auth::user()->account->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ Auth::user()->account->is_active ? 'Attivo' : 'Sospeso' }}
                            </span>
                        </p>
                    @else
                        <p class="text-warning">Nessun conto associato.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5>Azioni Rapide</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" disabled>Bonifico</button>
                        <button class="btn btn-info" disabled>Estratto Conto</button>
                        <button class="btn btn-warning" disabled>Ricarica Telefono</button>
                        <button class="btn btn-secondary" disabled>Impostazioni</button>
                    </div>
                    <small class="text-muted">Funzionalità in via di sviluppo</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5>Ultime Transazioni</h5>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(Auth::user()->account->allTransactions()->take(5)->get() as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $transaction->description ?? 'Transazione' }}</td>
                                        <td>
                                            @if($transaction->from_account_id === Auth::user()->account->id)
                                                <span class="text-danger">-€{{ number_format($transaction->amount, 2, ',', '.') }}</span>
                                            @else
                                                <span class="text-success">+€{{ number_format($transaction->amount, 2, ',', '.') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Nessuna transazione trovata.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection