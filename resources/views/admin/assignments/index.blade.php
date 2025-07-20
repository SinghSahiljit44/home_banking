@extends('layouts.bootstrap')

@section('title', 'Gestione Assegnazioni')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-tie me-2"></i>Gestione Assegnazioni Employee-Client</h2>
                <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard Admin
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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistiche -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-user-tie fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning">{{ $stats['total_employees'] }}</h4>
                    <p class="mb-0">Employees Totali</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-success mb-2"></i>
                    <h4 class="text-success">{{ $stats['total_clients'] }}</h4>
                    <p class="mb-0">Clienti Totali</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-link fa-2x text-info mb-2"></i>
                    <h4 class="text-info">{{ $stats['total_assignments'] }}</h4>
                    <p class="mb-0">Assegnazioni Attive</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-user-minus fa-2x text-danger mb-2"></i>
                    <h4 class="text-danger">{{ $stats['unassigned_clients'] }}</h4>
                    <p class="mb-0">Clienti Non Assegnati</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Nuova Assegnazione -->
    <div class="card bg-transparent border-light mb-4">
        <div class="card-header">
            <h5><i class="fas fa-plus me-2"></i>Nuova Assegnazione</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.assignments.assign') }}" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label for="employee_id" class="form-label">Employee *</label>
                    <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                        <option value="">Seleziona Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }} ({{ $employee->assigned_clients_count }} clienti)
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="client_id" class="form-label">Cliente *</label>
                    <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                        <option value="">Seleziona Cliente</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->full_name }} 
                                @if($client->assigned_employees_count > 0)
                                    (già assegnato)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="notes" class="form-label">Note</label>
                    <input type="text" class="form-control @error('notes') is-invalid @enderror" 
                        id="notes" name="notes" value="{{ old('notes') }}" 
                        placeholder="Note opzionali" maxlength="500">
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-link me-1"></i>Assegna
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assegnazione Multipla -->
    <div class="card bg-transparent border-secondary mb-4">
        <div class="card-header">
            <button class="btn btn-link text-light p-0" type="button" data-bs-toggle="collapse" data-bs-target="#bulkAssign">
                <h6><i class="fas fa-users me-2"></i>Assegnazione Multipla <i class="fas fa-chevron-down"></i></h6>
            </button>
        </div>
        <div class="collapse" id="bulkAssign">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.assignments.bulk-assign') }}" id="bulkAssignForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="bulk_employee_id" class="form-label">Employee *</label>
                            <select class="form-select" id="bulk_employee_id" name="employee_id" required>
                                <option value="">Seleziona Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->full_name }} ({{ $employee->assigned_clients_count }} clienti)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bulk_notes" class="form-label">Note</label>
                            <input type="text" class="form-control" id="bulk_notes" name="notes" 
                                   placeholder="Note per tutte le assegnazioni" maxlength="500">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Clienti da Assegnare *</label>
                        <div class="row">
                            @foreach($clients->where('assigned_employees_count', 0) as $client)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="client_ids[]" 
                                               value="{{ $client->id }}" id="client_{{ $client->id }}">
                                        <label class="form-check-label" for="client_{{ $client->id }}">
                                            {{ $client->full_name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">Seleziona i clienti non ancora assegnati</div>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-users me-1"></i>Assegna Selezionati
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Lista Assegnazioni Attive -->
    <div class="card bg-transparent border-light">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list me-2"></i>Assegnazioni Attive ({{ $assignments->total() }})</h5>
            </div>
        </div>
        <div class="card-body">
            @if($assignments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-dark table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Cliente</th>
                                <th>Assegnato da</th>
                                <th>Data Assegnazione</th>
                                <th>Note</th>
                                <th class="text-center">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $assignment)
                            <tr>
                                <td>
                                    <div>
                                        <div class="fw-bold">{{ $assignment->employee->full_name }}</div>
                                        <small class="text-muted">{{ $assignment->employee->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-bold">{{ $assignment->client->full_name }}</div>
                                        <small class="text-muted">{{ $assignment->client->email }}</small>
                                        @if($assignment->client->account)
                                            <br><small class="text-info">Conto: {{ $assignment->client->account->account_number }}</small>
                                        @else
                                            <br><small class="text-warning">Nessun conto</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div>{{ $assignment->assignedBy->full_name }}</div>
                                        <small class="text-muted">{{ ucfirst($assignment->assignedBy->role) }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $assignment->assigned_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $assignment->assigned_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    {{ $assignment->notes ?: '-' }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                onclick="confirmUnassign({{ $assignment->employee->id }}, {{ $assignment->client->id }}, '{{ $assignment->employee->full_name }}', '{{ $assignment->client->full_name }}')"
                                                title="Rimuovi Assegnazione">
                                            <i class="fas fa-unlink"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginazione -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Visualizzate {{ $assignments->firstItem() ?? 0 }} - {{ $assignments->lastItem() ?? 0 }} 
                        di {{ $assignments->total() }} assegnazioni
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($assignments->hasPages())
                            <small class="text-muted me-2">Pagina:</small>
                            <nav aria-label="Paginazione assegnazioni">
                                {{ $assignments->links('pagination::bootstrap-4') }}
                            </nav>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-link fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nessuna assegnazione presente</h5>
                    <p class="text-muted">Inizia creando la prima assegnazione Employee-Cliente.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Conferma Rimozione -->
<div class="modal fade" id="unassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Conferma Rimozione Assegnazione</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Sei sicuro di voler rimuovere l'assegnazione?</p>
                <p><strong>Employee:</strong> <span id="modal-employee-name"></span></p>
                <p><strong>Cliente:</strong> <span id="modal-client-name"></span></p>
                <p class="text-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Il cliente non sarà più gestito da questo employee.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <form id="unassignForm" method="POST" action="{{ route('admin.assignments.unassign') }}" class="d-inline">
                    @csrf
                    <input type="hidden" id="unassign_employee_id" name="employee_id">
                    <input type="hidden" id="unassign_client_id" name="client_id">
                    <button type="submit" class="btn btn-danger">Rimuovi Assegnazione</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmUnassign(employeeId, clientId, employeeName, clientName) {
    document.getElementById('unassign_employee_id').value = employeeId;
    document.getElementById('unassign_client_id').value = clientId;
    document.getElementById('modal-employee-name').textContent = employeeName;
    document.getElementById('modal-client-name').textContent = clientName;
    
    const modal = new bootstrap.Modal(document.getElementById('unassignModal'));
    modal.show();
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
@endsection