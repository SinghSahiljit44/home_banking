@extends('layouts.bootstrap')

@section('title', 'I Miei Beneficiari')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-address-book me-2"></i>I Miei Beneficiari</h2>
                <div>
                    <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addBeneficiaryModal">
                        <i class="fas fa-plus me-1"></i>Nuovo Beneficiario
                    </button>
                    <a href="{{ route('dashboard.cliente') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-1"></i>Dashboard
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
        <div class="col-12">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-users me-2"></i>Lista Beneficiari</h5>
                </div>
                <div class="card-body">
                    @forelse($beneficiaries as $beneficiary)
                        <div class="card bg-dark border-secondary mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-1">
                                        @if($beneficiary->is_favorite)
                                            <i class="fas fa-star text-warning fa-lg"></i>
                                        @else
                                            <i class="far fa-star text-muted"></i>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="mb-1">{{ $beneficiary->name }}</h6>
                                        @if($beneficiary->bank_name)
                                            <small class="text-muted">{{ $beneficiary->bank_name }}</small>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-0 font-monospace small">{{ $beneficiary->iban }}</p>
                                        @if($beneficiary->notes)
                                            <small class="text-muted">{{ Str::limit($beneficiary->notes, 50) }}</small>
                                        @endif
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">Aggiunto il {{ $beneficiary->created_at->format('d/m/Y') }}</small>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('client.transfer.create', ['beneficiary' => $beneficiary->id]) }}" 
                                               class="btn btn-sm btn-success" title="Invia Bonifico">
                                                <i class="fas fa-paper-plane"></i>
                                            </a>
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="editBeneficiary({{ $beneficiary->id }})" title="Modifica">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" 
                                                    onclick="toggleFavorite({{ $beneficiary->id }})" title="Preferito">
                                                <i class="fas fa-star"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="deleteBeneficiary({{ $beneficiary->id }})" title="Elimina">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-address-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nessun beneficiario salvato</h5>
                            <p class="text-muted">Aggiungi i tuoi beneficiari preferiti per velocizzare i bonifici.</p>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBeneficiaryModal">
                                <i class="fas fa-plus me-1"></i>Aggiungi il primo beneficiario
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aggiungi Beneficiario -->
<div class="modal fade" id="addBeneficiaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Nuovo Beneficiario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('client.beneficiaries.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome Beneficiario *</label>
                        <input type="text" class="form-control" id="name" name="name" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="iban" class="form-label">IBAN *</label>
                        <input type="text" class="form-control" id="iban" name="iban" required maxlength="34" 
                               placeholder="IT60 X054 2811 1010 0000 0123 456">
                    </div>
                    <div class="mb-3">
                        <label for="bank_name" class="form-label">Nome Banca</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Note</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_favorite" name="is_favorite" value="1">
                            <label class="form-check-label" for="is_favorite">
                                Aggiungi ai preferiti
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">Salva Beneficiario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editBeneficiary(id) {
    // Implementa modifica beneficiario
    alert('Funzionalità di modifica in sviluppo');
}

function toggleFavorite(id) {
    // Implementa toggle preferito
    fetch(`/client/beneficiaries/${id}/toggle-favorite`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }).then(() => location.reload());
}

function deleteBeneficiary(id) {
    if (confirm('Sei sicuro di voler eliminare questo beneficiario?')) {
        fetch(`/client/beneficiaries/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(() => location.reload());
    }
}

// Formatta IBAN
document.getElementById('iban').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '').toUpperCase();
    let formatted = value.replace(/(.{4})/g, '$1 ').trim();
    e.target.value = formatted;
});
</script>
@endsection