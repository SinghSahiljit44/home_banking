<div class="row">
    <!-- Informazioni Principali -->
    <div class="col-md-6">
        <h6><i class="fas fa-info-circle me-2"></i>Informazioni Generali</h6>
        
        <div class="mb-3">
            <label class="form-label text-muted small">Codice Riferimento:</label>
            <div class="font-monospace h6 text-info">{{ $data['reference_code'] }}</div>
        </div>
        
        <div class="row mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">Importo:</label>
                <div class="h5 text-success">€{{ $data['formatted_amount'] }}</div>
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Data e Ora:</label>
                <div class="small">{{ $data['created_at']->format('d/m/Y H:i:s') }}</div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">Tipo:</label>
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
                <label class="form-label text-muted small">Stato:</label>
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
    </div>

    <!-- Dettagli Movimento -->
    <div class="col-md-6">
        <h6><i class="fas fa-exchange-alt me-2"></i>Dettagli Movimento</h6>
        
        <!-- Da -->
        <div class="mb-3">
            <label class="form-label text-muted small">
                <i class="fas fa-arrow-up text-danger me-1"></i>Da:
            </label>
            <div class="bg-secondary bg-opacity-25 p-2 rounded">
                <div class="fw-bold small">{{ $data['from_user'] }}</div>
                @if($data['from_account'] !== '-')
                    <div class="font-monospace text-muted" style="font-size: 0.75rem;">{{ $data['from_account'] }}</div>
                @endif
            </div>
        </div>
        
        <!-- A -->
        <div class="mb-3">
            <label class="form-label text-muted small">
                <i class="fas fa-arrow-down text-success me-1"></i>A:
            </label>
            <div class="bg-secondary bg-opacity-25 p-2 rounded">
                <div class="fw-bold small">{{ $data['to_user'] }}</div>
                @if($data['to_account'] !== '-')
                    <div class="font-monospace text-muted" style="font-size: 0.75rem;">{{ $data['to_account'] }}</div>
                @endif
            </div>
        </div>
        
        <!-- Tipo di Operazione -->
        <div class="mb-3">
            <label class="form-label text-muted small">Tipo di Operazione:</label>
            <div>
                @if($transaction->fromAccount && $transaction->toAccount)
                    <span class="badge bg-info">Bonifico Interno</span>
                    <div class="text-muted" style="font-size: 0.75rem;">Tra conti della stessa banca</div>
                @else
                    <span class="badge bg-warning">Bonifico Esterno</span>
                    <div class="text-muted" style="font-size: 0.75rem;">Verso/da banca esterna</div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Descrizione completa -->
<div class="row mt-3">
    <div class="col-12">
        <label class="form-label text-muted small">Descrizione:</label>
        <div class="bg-secondary bg-opacity-25 p-3 rounded">
            {{ $data['description'] }}
        </div>
    </div>
</div>

<!-- IBAN completi se disponibili -->
@if($data['from_iban'] !== '-' || $data['to_iban'] !== '-')
<div class="row mt-3">
    <div class="col-12">
        <h6><i class="fas fa-university me-2"></i>Coordinate Bancarie</h6>
        @if($data['from_iban'] !== '-')
            <div class="mb-2">
                <label class="form-label text-muted small">IBAN Origine:</label>
                <div class="font-monospace small bg-secondary bg-opacity-25 p-2 rounded">{{ $data['from_iban'] }}</div>
            </div>
        @endif
        @if($data['to_iban'] !== '-')
            <div class="mb-2">
                <label class="form-label text-muted small">IBAN Destinazione:</label>
                <div class="font-monospace small bg-secondary bg-opacity-25 p-2 rounded">{{ $data['to_iban'] }}</div>
            </div>
        @endif
    </div>
</div>
@endif