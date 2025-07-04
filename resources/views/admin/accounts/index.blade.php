@extends('layouts.bootstrap')

@section('title', 'Gestione Conti')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-university me-2"></i>Gestione Conti</h2>
                <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard Admin
                </a>
            </div>
        </div>
    </div>

    <div class="card bg-transparent border-light">
        <div class="card-header">
            <h5><i class="fas fa-list me-2"></i>Conti ({{ $accounts->total() }})</h5>
        </div>
        <div class="card-body">
            @if($accounts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-dark table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Titolare</th>
                                <th>Numero Conto</th>
                                <th>IBAN</th>
                                <th class="text-end">Saldo</th>
                                <th>Stato</th>
                                <th>Creato</th>
                                <th class="text-center">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accounts as $account)
                            <tr>
                                <td>
                                    <div>
                                        <div class="fw-bold">{{ $account->user->full_name }}</div>
                                        <small class="text-muted">{{ $account->user->email }}</small>
                                    </div>
                                </td>
                                <td>{{ $account->account_number }}</td>
                                <td><span class="font-monospace">{{ $account->iban }}</span></td>
                                <td class="text-end">
                                    <span class="fw-bold text-success">€{{ number_format($account->balance, 2, ',', '.') }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $account->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $account->is_active ? 'Attivo' : 'Bloccato' }}
                                    </span>
                                </td>
                                <td>{{ $account->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        @if($account->is_active)
                                            <form method="POST" action="{{ route('admin.accounts.freeze', $account) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-warning" 
                                                        onclick="return confirm('Confermi il blocco di questo conto?')"
                                                        title="Blocca">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.accounts.unfreeze', $account) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        onclick="return confirm('Confermi lo sblocco di questo conto?')"
                                                        title="Sblocca">
                                                    <i class="fas fa-unlock"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-3">
                    {{ $accounts->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-university fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nessun conto trovato</h5>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection