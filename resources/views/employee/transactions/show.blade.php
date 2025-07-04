{{-- resources/views/employee/transactions/show.blade.php --}}
@extends('layouts.bootstrap')

@section('title', 'Dettagli Transazione')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-receipt me-2"></i>Dettagli Transazione</h2>
                <div>
                    <button onclick="window.print()" class="btn btn-outline-info me-2">
                        <i class="fas fa-print me-1"></i>Stampa
                    </button>
                    <a href="{{ route('employee.transactions.index') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i>Torna alle Transazioni
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informazioni Principali -->
        <div class="col-lg-6">
            <div class="card bg-transparent border-light mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle me-2"></i>Informazioni Generali</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted">Codice Riferimento:</label>
                            <div class="font-monospace h6 text-info">{{ $data['reference_code'] }}</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted">ID Transazione:</label>
                            <div class="h6">#{{ $data['id'] }}</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted">Importo:</label>
                            <div class="h4 text-success">€{{ $data['formatted_amount'] }}</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted">Data e Ora:</label>
                            <div>{{ $data['created_at']->format('d/m/Y H:i:s') }}</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted">Tipo Transazione:</label>
                            <div>
                                @if($data['type'] === 'transfer_in')
                                    <span class="badge bg-success">Bonifico Ricevuto</span>
                                @elseif($data['type'] === 'transfer_out')
                                    <span class="badge bg-primary">Bonifico Inviato</span>
                                @elseif($data['type'] === 'deposit')
                                    <span class="badge bg-info">Deposito</span>
                                @elseif($data['type'] === 'withdrawal')
                                    <span class="badge bg-warning">Prelievo</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($data['type']) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted">Stato:</label>
                            <div>
                                @if($data['status'] === 'completed')
                                    <span class="badge bg-success">Completato</span>
                                @elseif($data['status'] === 'pending')
                                    <span class="badge bg-warning">In Elaborazione</span>
                                @elseif($data['status'] === 'failed')
                                    <span class="badge bg-danger">Fallito</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($data['status']) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Descrizione:</label>
                        <div class="bg-dark p-3 rounded">{{ $data['description'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dettagli Movimento -->
        <div class="col-lg-6">
            <div class="card bg-transparent border-light mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-exchange-alt me-2"></i>Dettagli Movimento</h5>
                </div>
                <div class="card-body">
                    <!-- Conto di Origine -->
                    <div class="mb-4">
                        <label class="form-label text-muted">
                            <i class="fas fa-arrow-up text-danger me-1"></i>Da:
                        </label>
                        <div class="bg-dark p-3 rounded">
                            <div class="fw-bold">{{ $data['from_user'] }}</div>
                            @if($data['from_account'] !== '-')
                                <div class="font-monospace small text-muted">Conto: {{ $data['from_account'] }}</div>
                                <div class="font-monospace small text-muted">IBAN: {{ $data['from_iban'] }}</div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Conto di Destinazione -->
                    <div class="mb-4">
                        <label class="form-label text-muted">
                            <i class="fas fa-arrow-down text-success me-1"></i>A:
                        </label>
                        <div class="bg-dark p-3 rounded">
                            <div class="fw-bold">{{ $data['to_user'] }}</div>
                            @if($data['to_account'] !== '-')
                                <div class="font-monospace small text-muted">Conto: {{ $data['to_account'] }}</div>
                                <div class="font-monospace small text-muted">IBAN: {{ $data['to_iban'] }}</div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Tipo di Operazione -->
                    <div class="mb-3">
                        <label class="form-label text-muted">Tipo di Operazione:</label>
                        <div>
                            @if($transaction->fromAccount && $transaction->toAccount)
                                <span class="badge bg-info">Bonifico Interno</span>
                                <div class="small text-muted">Trasferimento tra conti della stessa banca</div>
                            @else
                                <span class="badge bg-warning">Bonifico Esterno</span>
                                <div class="small text-muted">Trasferimento verso/da banca esterna</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline della Transazione -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-clock me-2"></i>Cronologia</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Transazione Creata</h6>
                                <p class="text-muted mb-1">{{ $data['created_at']->format('d/m/Y H:i:s') }}</p>
                                <small class="text-muted">Transazione registrata nel sistema</small>
                            </div>
                        </div>
                        
                        @if($data['status'] === 'completed')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Transazione Completata</h6>
                                <p class="text-muted mb-1">{{ $data['created_at']->format('d/m/Y H:i:s') }}</p>
                                <small class="text-muted">Fondi trasferiti con successo</small>
                            </div>
                        </div>
                        @elseif($data['status'] === 'pending')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">In Elaborazione</h6>
                                <p class="text-muted mb-1">Attualmente</p>
                                <small class="text-muted">Transazione in attesa di completamento</small>
                            </div>
                        </div>
                        @elseif($data['status'] === 'failed')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Transazione Fallita</h6>
                                <p class="text-muted mb-1">{{ $data['created_at']->format('d/m/Y H:i:s') }}</p>
                                <small class="text-muted">La transazione non è stata completata</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Timeline styling */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: -31px;
    top: 17px;
    bottom: -20px;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
}

/* Print styles */
@media print {
    .btn, .card-header {
        display: none !important;
    }
    
    body {
        background: white !important;
        color: black !important;
    }
    
    .card {
        border: 1px solid #ccc !important;
        background: white !important;
    }
}
</style>
@endsection