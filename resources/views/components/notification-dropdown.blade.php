<div class="dropdown">
    <button class="btn btn-outline-light position-relative" type="button" data-bs-toggle="dropdown">
        <i class="fas fa-bell"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>
    
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
        <div class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifiche</span>
            @if($unreadCount > 0)
                <button class="btn btn-sm btn-link p-0" onclick="markAllAsRead()">
                    Segna tutte come lette
                </button>
            @endif
        </div>
        
        @forelse($notifications as $notification)
            <div class="dropdown-item {{ $notification->read_at ? '' : 'bg-light' }} p-3">
                <div class="d-flex">
                    <div class="flex-shrink-0 me-2">
                        @switch($notification->type)
                            @case('transaction')
                                <i class="fas fa-exchange-alt text-primary"></i>
                                @break
                            @case('security')
                                <i class="fas fa-shield-alt text-warning"></i>
                                @break
                            @case('system')
                                <i class="fas fa-cog text-info"></i>
                                @break
                            @default
                                <i class="fas fa-bell text-muted"></i>
                        @endswitch
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $notification->title }}</h6>
                        <p class="mb-1 small">{{ Str::limit($notification->message, 80) }}</p>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                    @if(!$notification->read_at)
                        <div class="flex-shrink-0">
                            <span class="badge bg-primary rounded-pill"></span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="dropdown-item text-center text-muted py-4">
                <i class="fas fa-bell-slash fa-2x mb-2"></i>
                <p class="mb-0">Nessuna notifica</p>
            </div>
        @endforelse
        
        @if($notifications->count() > 0)
            <div class="dropdown-divider"></div>
            <div class="dropdown-item text-center">
                <a href="{{ route('client.notifications.index') }}" class="btn btn-sm btn-primary">
                    Visualizza tutte
                </a>
            </div>
        @endif
    </div>
</div>

<style>
.notification-dropdown {
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.notification-dropdown .dropdown-item:hover {
    background-color: rgba(0, 123, 255, 0.1);
}
</style>

<script>
function markAllAsRead() {
    fetch('/client/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }).then(() => location.reload());
}
</script> text-danger">403</h1>
                    <h3 class="mb-3">Accesso Negato</h3>
                    <p class="text-white-50 mb-4">Non hai i permessi necessari per accedere a questa risorsa.</p>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ url()->previous() }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Torna Indietro
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-light">
                            <i class="fas fa-home me-2"></i>Vai alla Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection