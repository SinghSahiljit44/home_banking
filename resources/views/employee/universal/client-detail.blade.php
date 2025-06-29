@extends('layouts.bootstrap')

@section('title', 'Dettaglio Cliente per Deposito')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user me-2"></i>{{ $client->full_name }}</h2>
                <div>
                    @if($isAssignedClient)
                        <span class="badge bg-info me-2">Cliente Assegnato</span>
                    @endif
                    <a href="{{ route('employee.universal.clients') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i>Torna alla Lista
                    </a>
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

    <div class="row">
        <!-- Informazioni Cliente -->
        <div class="col-lg-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-id-card me-2"></i>Informazioni Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nome Completo:</strong> {{ $client->full_name }}</p>
                            <p><strong>Username:</strong> {{ $client->username }}</p>
                            <p><strong>Email:</strong> {{ $client->email }}</p>
                            <p><strong>Telefono:</strong> {{ $client->phone ?: 'Non specificato' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Stato:</strong> 
                                <span class="badge {{ $client->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $client->is_active ? 'Attivo' : 'Sospeso' }}
                                </span>
                            </p>
                            <p><strong>Registrato:</strong> {{ $client->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Ultimo Aggiornamento:</strong> {{ $client->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @if($client->address)
                        <div class="row">
                            <div class="col-12">
                                <p><strong>Indirizzo:</strong> {{ $client->address }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Informazioni Conto -->
            @if($client->account)
                <div class="card bg-transparent border-light mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-university me-2"></i>Conto Corrente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Numero Conto:</strong> {{ $client->account->account_number }}</p>
                                <p><strong>IBAN:</strong> <span class="font-monospace">{{ $client->account->iban }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Saldo:</strong> 
                                    <span class="h5 text-success">€{{ number_format($client->account->balance, 2, ',', '.') }}</span>
                                </p>
                                <p><strong>Stato Conto:</strong> 
                                    <span class="badge {{ $client->account->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $client->account->is_active ? 'Attivo' : 'Bloccato' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card bg-transparent border-warning mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Nessun Conto Associato</h5>
                    </div>
                    <div class="card-body">
                        <p>Questo cliente non ha ancora un conto corrente associato.</p>
                    </div>
                </div>
            @endif

            <!-- Transazioni Recenti (solo se assegnato) -->
            @if($isAssignedClient && $recentTransactions->count() > 0)
                <div class="card bg-transparent border-light mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-history me-2"></i>Transazioni Recenti</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-dark table-striped">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Tipo</th>
                                        <th>Descrizione</th>
                                        <th class="text-end">Importo</th>
                                        <th>Stato</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($transaction->from_account_id === $client->account->id)
                                                <span class="badge bg-primary">Uscita</span>
                                            @else
                                                <span class="badge bg-success">Entrata</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($transaction->description, 40) }}</td>
                                        <td class="text-end">
                                            @if($transaction->from_account_id === $client->account->id)
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
                    </div>
                </div>
            @endif
        </div>

        <!-- Azioni e Statistiche -->
        <div class="col-lg-4">
            <!-- Statistiche Cliente -->
            @if($clientStats)
                <div class="card bg-transparent border-light">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar me-2"></i>Statistiche</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Totale Transazioni:</strong> {{ $clientStats['total_transactions'] }}</p>
                        <p><strong>Entrate Totali:</strong> 
                            <span class="text-success">€{{ number_format($clientStats['total_incoming'], 2, ',', '.') }}</span>
                        </p>
                        <p><strong>Saldo Corrente:</strong> 
                            <span class="text-info">€{{ number_format($clientStats['current_balance'], 2, ',', '.') }}</span>
                        </p>
                        @if($clientStats['last_transaction'])
                            <p><strong>Ultima Transazione:</strong> {{ $clientStats['last_transaction']->created_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Azioni di Deposito -->
            @if($client->account && $client->account->is_active && $client->is_active)
                <div class="card bg-transparent border-light mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-plus-circle me-2"></i>Deposito</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('employee.universal.deposit', $client) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="amount" class="form-label">Importo (€) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="amount" 
                                           name="amount" 
                                           step="0.01" 
                                           min="0.01" 
                                           max="100000" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Descrizione *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="description" 
                                       name="description" 
                                       value="Deposito amministrativo" 
                                       maxlength="255" 
                                       required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" 
                                        class="btn btn-success"
                                        onclick="return confirm('Confermi il deposito per {{ $client->full_name }}?')">
                                    <i class="fas fa-plus-circle me-1"></i>Effettua Deposito
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="card bg-transparent border-warning mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Deposito Non Disponibile</h5>
                    </div>
                    <div class="card-body">
                        @if(!$client->account)
                            <p>Il cliente non ha un conto corrente.</p>
                        @elseif(!$client->account->is_active)
                            <p>Il conto del cliente è bloccato.</p>
                        @else
                            <p>Il cliente è disattivato.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection