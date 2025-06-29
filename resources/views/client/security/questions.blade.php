@extends('layouts.bootstrap')

@section('title', 'Domande di Sicurezza')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-question-circle me-2"></i>Domande di Sicurezza</h4>
                        <a href="{{ route('client.profile.show') }}" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Torna al Profilo
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Perché configurare una domanda di sicurezza?</strong><br>
                        Le domande di sicurezza aggiungono un ulteriore livello di protezione al tuo account e possono essere utilizzate per il recupero dell'accesso in caso di problemi.
                    </div>

                    @if($securityQuestion)
                        <!-- Domanda Esistente -->
                        <div class="card bg-dark border-success mb-4">
                            <div class="card-header bg-success">
                                <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Domanda di Sicurezza Attiva</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-3"><strong>Domanda configurata:</strong></p>
                                <p class="text-info mb-4">{{ $securityQuestion->question }}</p>
                                
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#verifyModal">
                                        <i class="fas fa-check me-2"></i>Verifica Risposta
                                    </button>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#changeModal">
                                        <i class="fas fa-edit me-2"></i>Cambia Domanda
                                    </button>
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash me-2"></i>Rimuovi
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Nessuna Domanda Configurata -->
                        <div class="card bg-dark border-warning mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Nessuna Domanda Configurata</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">Non hai ancora configurato una domanda di sicurezza. Ti consigliamo di farlo per aumentare la sicurezza del tuo account.</p>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#setupModal">
                                    <i class="fas fa-plus me-2"></i>Configura Domanda di Sicurezza
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Suggerimenti Sicurezza -->
                    <div class="card bg-dark border-secondary">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Consigli per la Sicurezza</h6>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li>Scegli una domanda di cui solo tu puoi conoscere la risposta</li>
                                <li>Evita informazioni facilmente reperibili sui social media</li>
                                <li>Usa una risposta che non cambierà nel tempo</li>
                                <li>Non condividere mai la risposta con nessuno</li>
                                <li>Considera di usare una risposta non ovvia ma memorabile</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Setup/Change -->
<div class="modal fade" id="setupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">{{ $securityQuestion ? 'Cambia' : 'Configura' }} Domanda di Sicurezza</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('client.security.questions.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="question" class="form-label">Seleziona una domanda *</label>
                        <select class="form-select" id="question" name="question" required>
                            <option value="">Scegli una domanda...</option>
                            @foreach($availableQuestions as $q)
                                <option value="{{ $q }}">{{ $q }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="answer" class="form-label">La tua risposta *</label>
                        <input type="text" 
                               class="form-control" 
                               id="answer" 
                               name="answer" 
                               required 
                               minlength="3" 
                               maxlength="100"
                               placeholder="Inserisci la tua risposta">
                        <div class="form-text">Minimo 3 caratteri, massimo 100</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password attuale *</label>
                        <input type="password" 
                               class="form-control" 
                               id="current_password" 
                               name="current_password" 
                               required
                               placeholder="Conferma la tua identità">
                        <div class="form-text">Richiesta per motivi di sicurezza</div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attenzione:</strong> Assicurati di ricordare la risposta esatta, incluse maiuscole e minuscole.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>{{ $securityQuestion ? 'Aggiorna' : 'Configura' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Change (alias per setup) -->
<div class="modal fade" id="changeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Cambia Domanda di Sicurezza</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('client.security.questions.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        La domanda attuale verrà sostituita con quella nuova.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Domanda attuale:</label>
                        <p class="text-muted">{{ $securityQuestion->question ?? 'Nessuna' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="question_change" class="form-label">Nuova domanda *</label>
                        <select class="form-select" id="question_change" name="question" required>
                            <option value="">Scegli una domanda...</option>
                            @foreach($availableQuestions as $q)
                                <option value="{{ $q }}" {{ ($securityQuestion && $securityQuestion->question === $q) ? 'selected' : '' }}>
                                    {{ $q }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="answer_change" class="form-label">Nuova risposta *</label>
                        <input type="text" 
                               class="form-control" 
                               id="answer_change" 
                               name="answer" 
                               required 
                               minlength="3" 
                               maxlength="100"
                               placeholder="Inserisci la nuova risposta">
                    </div>
                    
                    <div class="mb-3">
                        <label for="current_password_change" class="form-label">Password attuale *</label>
                        <input type="password" 
                               class="form-control" 
                               id="current_password_change" 
                               name="current_password" 
                               required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Aggiorna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Verify -->
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title">Verifica Risposta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('client.security.verify.check') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Domanda:</label>
                        <p class="text-info">{{ $securityQuestion->question ?? '' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="verify_answer" class="form-label">La tua risposta *</label>
                        <input type="text" 
                               class="form-control" 
                               id="verify_answer" 
                               name="answer" 
                               required
                               placeholder="Inserisci la tua risposta">
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Inserisci la risposta esattamente come l'hai configurata.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-check me-2"></i>Verifica
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Rimuovi Domanda di Sicurezza</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('client.security.questions.destroy') }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attenzione!</strong> Stai per rimuovere la domanda di sicurezza. Questa azione non può essere annullata.
                    </div>
                    
                    <p>Domanda da rimuovere:</p>
                    <p class="text-info">{{ $securityQuestion->question ?? '' }}</p>
                    
                    <div class="mb-3">
                        <label for="current_password_delete" class="form-label">Password attuale *</label>
                        <input type="password" 
                               class="form-control" 
                               id="current_password_delete" 
                               name="current_password" 
                               required
                               placeholder="Conferma la tua identità">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Rimuovi Definitivamente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-clear modals on hide
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
            }
        });
    });
    
    // Prevent form submission on Enter in answer fields (to avoid accidental submissions)
    document.querySelectorAll('input[name="answer"]').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                // Focus next input or submit button
                const form = this.closest('form');
                const nextInput = form.querySelector('input[name="current_password"]');
                if (nextInput && !nextInput.value) {
                    nextInput.focus();
                } else {
                    form.querySelector('button[type="submit"]').focus();
                }
            }
        });
    });
});
</script>
@endsection