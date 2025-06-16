<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Deposito</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 1rem;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .border-start-5 {
            border-left-width: 5px !important;
        }

        .chart-container {
            position: relative;
            height: 400px;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <h2 class="mb-4 text-center">Dashboard Deposito</h2>

        <!-- Stat Cards -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-start border-primary border-start-5 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Jumlah Deposan</h5>
                        <h3>{{ $jumlahDeposan }}</h3>
                        <p class="text-muted">Jumlah Deposan Aktif per: {{ $tanggal }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-start border-success border-start-5 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Saldo Deposito</h5>
                        <h3>Rp. {{ number_format($totalSaldo, 0, ',', '.') }}</h3>
                        <p class="text-muted">Total Saldo Deposito</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-start border-warning border-start-5 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Bunga Deposito</h5>
                        <h3>Rp. {{ number_format($totalBunga, 0, ',', '.') }}</h3>
                        <p class="text-muted">Total Bunga Deposito</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-start border-danger border-start-5 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Pajak Deposito</h5>
                        <h3>Rp. {{ number_format($totalPajak, 0, ',', '.') }}</h3>
                        <p class="text-muted">Total Pajak Deposito</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-start border-info border-start-5 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Bayar Deposito</h5>
                        <h3>Rp. {{ number_format($totalBayar, 0, ',', '.') }}</h3>
                        <p class="text-muted">Total Bayar Deposito</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-start border-secondary border-start-5 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Jatuh Tempo Bulan Ini</h5>
                        <h3>{{ $jatuhTempoBulanIni }}</h3>
                        <p class="text-muted">Total Jatuh Tempo Bulan Ini</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-start border-dark border-start-5 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Bayar Bunga Hari Ini</h5>
                        <h3>Rp. {{ number_format($totalBayarHariIni, 0, ',', '.') }}</h3>
                        <p class="text-muted">Pembayaran bunga deposito setiap tanggal {{ now()->format('d') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-start border-dark border-start-5 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Bayar Hari Esok</h5>
                        <h3>Rp. {{ number_format($totalBayarHariEsok, 0, ',', '.') }}</h3>
                        <p class="text-muted">Bayar Bunga Deposito Jatuh Tempo Hari Esok</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <hr class="my-5">
        <h4 class="mb-3 text-center">Top 10 Nasabah Berdasarkan Total Deposito</h4>
        <div class="chart-container">
            <canvas id="nasabahChart"></canvas>
        </div>
    </div>

    <!-- Chart Rendering -->
    <script>
        const ctx = document.getElementById('nasabahChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($topNasabah->pluck('nasabah_nama_lengkap')) !!},
                datasets: [{
                    label: 'Total Deposito (Rp)',
                    data: {!! json_encode($topNasabah->pluck('total_valuta')) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp. ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp. ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
