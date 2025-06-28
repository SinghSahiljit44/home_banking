@extends('layouts.bootstrap')

@section('title', 'Dashboard Dipendente')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Dashboard Dipendente</h2>
                <div>
                    <span class="text-white-50 me-3">Benvenuto, {{ Auth::user()->full_name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <h5 class="card-title">Clienti Gestiti</h5>
                    <h3 class="text-success">{{ \App\Models\User::where('role', 'client')->where('is_active', true)->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <h5 class="card-title">Transazioni Oggi</h5>
                    <h3 class="text-info">{{ \App\Models\Transaction::whereDate('created_at', today())->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h3 class="text-warning">{{ \App\Models\Transaction::where('status', 'pending')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <h5 class="card-title">Volume Oggi</h5>
                    <h3 class="text-primary">€{{ number_format(\App\Models\Transaction::whereDate('created_at', today())->sum('amount'), 2, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5>Transazioni Recenti</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-striped">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Cliente</th>
                                    <th>Importo</th>
                                    <th>Tipo</th>
                                    <th>Stato</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\Transaction::with(['fromAccount.user', 'toAccount.user'])->latest()->take(10)->get() as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $transaction->fromAccount ? $transaction->fromAccount->user->full_name : ($transaction->toAccount ? $transaction->toAccount->user->full_name : 'Sistema') }}</td>
                                    <td>€{{ number_format($transaction->amount, 2, ',', '.') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</td>
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
                        <button class="btn btn-primary" disabled>Gestisci Transazioni</button>
                        <button class="btn btn-success" disabled>Supporto Clienti</button>
                        <button class="btn btn-info" disabled>Report</button>
                        <button class="btn btn-warning" disabled>Verifica Documenti</button>
                    </div>
                    <small class="text-muted">Funzionalità in via di sviluppo</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection