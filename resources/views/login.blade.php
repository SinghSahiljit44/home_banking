@extends('layouts.bootstrap')

@section('title', 'Login')

@section('content')
<div class="container text-center" style="margin-top: 100px;">
  <h2 class="mb-5">Seleziona il tipo di accesso</h2>
  
  @if ($errors->any())
    <div class="alert alert-danger">
      @foreach ($errors->all() as $error)
        <p class="mb-0">{{ $error }}</p>
      @endforeach
    </div>
  @endif

  @if (session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  <div class="row justify-content-center g-4">
    <div class="col-md-4">
      <div class="choice-card">
        <h4>Accesso Cliente</h4>
        <p class="text-white-50">Controlla conti, carte e operazioni personali.</p>
        <a href="{{ url('/login-cliente') }}" class="btn btn-success w-100 mt-3">Login Cliente</a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="choice-card">
        <h4>Accesso Lavoratore</h4>
        <p class="text-white-50">Accedi alla tua area come dipendente.</p>
        <a href="{{ url('/login-lavoratore') }}" class="btn btn-primary w-100 mt-3">Login Lavoratore</a>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
// Script per prevenire il back button dopo logout forzato
(function() {
    'use strict';
    
    // Previene il back button
    function preventBackButton() {
        // Aggiungi una entry alla history per "intrappolare" l'utente
        history.pushState(null, null, location.href);
        
        // Gestisci l'evento popstate (back button)
        window.addEventListener('popstate', function(event) {
            // Log del tentativo
            console.warn('Back button attempt blocked');
            
            // Riporta l'utente alla pagina corrente
            history.pushState(null, null, location.href);
            
            // Mostra un avviso
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Accesso Negato',
                    text: 'Non è possibile tornare indietro per motivi di sicurezza.',
                    confirmButtonText: 'OK'
                });
            } else {
                alert('Non è possibile tornare indietro per motivi di sicurezza.');
            }
        });
    }
    
    // Previene la cache della pagina
    function preventPageCache() {
        // Forza il reload se la pagina viene caricata dalla cache
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
        
        // Previene la cache con meta tag dinamici
        const meta1 = document.createElement('meta');
        meta1.httpEquiv = 'Cache-Control';
        meta1.content = 'no-cache, no-store, must-revalidate';
        document.head.appendChild(meta1);
        
        const meta2 = document.createElement('meta');
        meta2.httpEquiv = 'Pragma';
        meta2.content = 'no-cache';
        document.head.appendChild(meta2);
        
        const meta3 = document.createElement('meta');
        meta3.httpEquiv = 'Expires';
        meta3.content = '0';
        document.head.appendChild(meta3);
    }
    
    // Disabilita i tasti di navigazione
    function preventKeyboardNavigation() {
        document.addEventListener('keydown', function(event) {
            // Previene Alt+Left (back), Alt+Right (forward)
            if (event.altKey && (event.keyCode === 37 || event.keyCode === 39)) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
            
            // Previene Backspace se non è in un input
            if (event.keyCode === 8) {
                const element = event.target || event.srcElement;
                const tagName = element.tagName.toLowerCase();
                
                if (tagName !== 'input' && tagName !== 'textarea' && !element.isContentEditable) {
                    event.preventDefault();
                    event.stopPropagation();
                    return false;
                }
            }
        });
    }
    
    // Gestisci il beforeunload per pulire la sessione
    function handlePageUnload() {
        window.addEventListener('beforeunload', function() {
            // Invia una richiesta per pulire la sessione se necessario
            if (navigator.sendBeacon) {
                const data = new FormData();
                data.append('action', 'cleanup_session');
                navigator.sendBeacon('/api/session-cleanup', data);
            }
        });
    }
    
    // Controlla se siamo in una pagina di login dopo logout forzato
    function checkForceLogoutState() {
        const urlParams = new URLSearchParams(window.location.search);
        const hasErrors = document.querySelector('.alert-danger, .error-message');
        
        if (hasErrors) {
            // Se ci sono errori di sicurezza, attiva tutte le protezioni
            preventBackButton();
            preventKeyboardNavigation();
        }
    }
    
    // Inizializza tutto quando la pagina è pronta
    document.addEventListener('DOMContentLoaded', function() {
        preventPageCache();
        checkForceLogoutState();
        handlePageUnload();
        
        // Controlla se siamo in una pagina di login
        const isLoginPage = window.location.pathname.includes('login');
        
        if (isLoginPage) {
            // Su pagine di login, attiva sempre le protezioni
            preventBackButton();
            
            // Controlla se c'è un messaggio di errore di sicurezza
            const securityError = document.querySelector('[data-security-error]');
            if (securityError) {
                preventKeyboardNavigation();
            }
        }
    });
    
    // Monitora i cambiamenti nella history
    const originalPushState = history.pushState;
    const originalReplaceState = history.replaceState;
    
    history.pushState = function() {
        originalPushState.apply(history, arguments);
        window.dispatchEvent(new Event('locationchange'));
    };
    
    history.replaceState = function() {
        originalReplaceState.apply(history, arguments);
        window.dispatchEvent(new Event('locationchange'));
    };
    
    // Ascolta i cambiamenti di location
    window.addEventListener('locationchange', function() {
        // Se siamo su una pagina protetta senza autenticazione, reindirizza
        const protectedPaths = ['/dashboard', '/admin', '/employee', '/client'];
        const currentPath = window.location.pathname;
        
        if (protectedPaths.some(path => currentPath.includes(path))) {
            // Verifica se l'utente è autenticato (questo dovrebbe essere gestito dal server)
            fetch('/api/auth-check', { 
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    // Se non autenticato, determina quale pagina di login usare
                    if (currentPath.includes('admin') || currentPath.includes('employee')) {
                        window.location.href = '/login-lavoratore';
                    } else if (currentPath.includes('client')) {
                        window.location.href = '/login-cliente';  
                    } else {
                        window.location.href = '/login';
                    }
                }
            })
            .catch(() => {
                // In caso di errore, redirect alla pagina principale di login
                window.location.href = '/login';
            });
        }
    });
    
})();

</script>
@endpush

<!-- Aggiungi anche questi meta tag nell'head delle view di login -->
@push('head')
<meta http-equiv="Cache-Control" content="no-cache, no-store, max-age=0, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Fri, 01 Jan 1990 00:00:00 GMT">
<meta name="robots" content="noindex, nofollow, nosnippet, noarchive">

@if($errors->has('access') || $errors->has('security'))
<meta name="security-error" content="true">
@endif
@endpush

@endsection