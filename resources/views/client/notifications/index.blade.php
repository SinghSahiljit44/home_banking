@extends('layouts.bootstrap')

@section('title', 'Le Mie Notifiche')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bell me-2"></i>Le Mie Notifiche</h2>
                <div>
                    @if($notifications->where('read_at', null)->count() > 0)
                        <button class="btn btn-outline-primary me-2" onclick="markAllAsRead()">
                            <i class="fas fa-check-double me-1"></i>Segna tutte come lette
                        </button>
                    @endif
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
                    <div class="d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-list me-2"></i>Tutte le Notifiche</h5>
                        <span class="badge bg-primary">{{ $notifications->total() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @forelse($notifications as $notification)
                        <div class="card bg-dark border-secondary mb-3 {{ $notification->read_at ? 'opacity-75' : '' }}">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-1 text-center">
                                        @switch($notification->type)
                                            @case('transaction')
                                                <i class="fas fa-exchange-alt fa-lg text-primary"></i>
                                                @break
                                            @case('security')
                                                <i class="fas fa-shield-alt fa-lg text-warning"></i>
                                                @break
                                            @case('system')
                                                <i class="fas fa-cog fa-lg text-info"></i>
                                                @break
                                            @case('email')
                                                <i class="fas fa-envelope fa-lg text-success"></i>
                                                @break
                                            @default
                                                <i class="fas fa-bell fa-lg text-muted"></i>
                                        @endswitch
                                    </div>
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center mb-1">
                                            <h6 class="mb-0 me-2">{{ $notification->title }}</h6>
                                            @if($notification->is_important)
                                                <span class="badge bg-danger">Importante</span>
                                            @endif
                                            @if(!$notification->read_at)
                                                <span class="badge bg-primary ms-2">Nuova</span>
                                            @endif
                                        </div>
                                        <p class="mb-1">{{ $notification->message }}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $notification->created_at->format('d/m/Y H:i') }} 
                                            ({{ $notification->created_at->diffForHumans() }})
                                        </small>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <div class="btn-group" role="group">
                                            @if(!$notification->read_at)
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="markAsRead({{ $notification->id }})" 
                                                        title="Segna come letta">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteNotification({{ $notification->id }})" 
                                                    title="Elimina">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nessuna notifica</h5>
                            <p class="text-muted">Non hai ancora ricevuto notifiche.</p>
                        </div>
                    @endforelse
                </div>
                
                @if($notifications->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-center">
                            {{ $notifications->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function markAsRead(id) {
    fetch(`/client/notifications/${id}/mark-as-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }).then(() => location.reload());
}

function markAllAsRead() {
    fetch('/client/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }).then(() => location.reload());
}

function deleteNotification(id) {
    if (confirm('Sei sicuro di voler eliminare questa notifica?')) {
        fetch(`/client/notifications/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }).then(() => location.reload());
    }
}
</script>
@endsection
