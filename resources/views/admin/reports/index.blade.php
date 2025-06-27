@extends('layouts.bootstrap')

@section('title', 'Report e Statistiche')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-bar me-2"></i>Report e Statistiche</h2>
                <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard Admin
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiche Generali -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h4 class="text-success">{{ number_format($stats['total_users']) }}</h4>
                    <p class="mb-0">Utenti Totali</p>
                    <small class="text-muted">{{ $stats['active_users'] }} attivi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-university fa-2x text-info mb-2"></i>
                    <h4 class="text-info">{{ number_format($stats['total_accounts']) }}</h4>
                    <p class="mb-0">Conti Totali</p>
                    <small class="text-muted">{{ $stats['active_accounts'] }} attivi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-exchange-alt fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning">{{ number_format($stats['total_transactions']) }}</h4>
                    <p class="mb-0">Transazioni</p>
                    <small class="text-muted">{{ $stats['transactions_today'] }} oggi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-transparent border-light text-center">
                <div class="card-body">
                    <i class="fas fa-euro-sign fa-2x text-success mb-2"></i>
                    <h4 class="text-success">€{{ number_format($stats['total_balance'], 2, ',', '.') }}</h4>
                    <p class="mb-0">Saldo Totale</p>
                    <small class="text-muted">€{{ number_format($stats['volume_today'], 2, ',', '.') }} oggi</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Report -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-list me-2"></i>Report Disponibili</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-dark border-secondary h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-exchange-alt fa-3x text-primary mb-3"></i>
                                    <h6>Report Transazioni</h6>
                                    <p class="small text-muted">Analisi dettagliata di tutte le transazioni</p>
                                    <a href="{{ route('admin.reports.transactions') }}" class="btn btn-primary btn-sm">
                                        Visualizza Report
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-dark border-secondary h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-3x text-info mb-3"></i>
                                    <h6>Report Utenti</h6>
                                    <p class="small text-muted">Statistiche e analisi degli utenti</p>
                                    <a href="{{ route('admin.reports.users') }}" class="btn btn-info btn-sm">
                                        Visualizza Report
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-dark border-secondary h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-university fa-3x text-success mb-3"></i>
                                    <h6>Report Conti</h6>
                                    <p class="small text-muted">Analisi dei conti correnti</p>
                                    <button class="btn btn-success btn-sm" disabled>
                                        In Sviluppo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafico Transazioni Mensili -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-transparent border-light">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line me-2"></i>Andamento Transazioni (Ultimi 12 Mesi)</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Grafico transazioni mensili
const ctx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            @foreach($monthlyTransactions->reverse() as $month)
                '{{ $month->month }}/{{ $month->year }}',
            @endforeach
        ],
        datasets: [{
            label: 'Numero Transazioni',
            data: [
                @foreach($monthlyTransactions->reverse() as $month)
                    {{ $month->count }},
                @endforeach
            ],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    color: 'white'
                }
            }
        },
        scales: {
            y: {
                ticks: {
                    color: 'white'
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                }
            },
            x: {
                ticks: {
                    color: 'white'
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                }
            }
        }
    }
});
</script>
@endsection