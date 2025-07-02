<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="300"> <!-- Refresh setiap 5 menit -->
    <title>Dashboard Deposito</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<div id="loadingOverlay">
    <div class="spinner"></div>
</div>

<body>

    <div class="container py-3">

        <!-- Logo kiri atas -->
        {{-- <div class="d-flex align-items-center mb-3">
            <img src="{{ asset('logo.png') }}" alt="Logo" height="50">
        </div> --}}

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="Logo" height="50">
                {{-- <strong>Dashboard Deposito</strong> --}}
            </div>
            <strong><div style="padding-right: 15px" id="timestamp" class="text-muted large"></div></strong>
        </div>


        <!-- Judul Dashboard -->
        <div style="font-size: 50px" class="dashboard-title text-center mb-4">DASHBOARD DEPOSITO</div>

        <!-- Cards -->
        <div class="row mt-3 g-3">
            <div class="col-12 col-md-6">
                <div class="card bg-light text-white" style="background-color: #aeb0b0 !important;">
                    <!-- Icon transparan di background kanan atas -->
                    <div class="icon-bg">

                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><strong>{{ $totalDeposito }}</strong></h3>
                        <p style="font-size: 20px" class="card-text"><strong>Nasabah Deposito Aktif</strong><br>
                            <small><a href="{{ route('deposito.list') }}"
                                    class="text-white text-decoration-underline">Details</a></small>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card bg-light text-white" style="background-color: #28a745 !important;">
                    <div class="card-body">
                        <div class="icon-bg">
                            <i class="fa-solid fa-money-bill-trend-up"></i>
                        </div>
                        <h3 class="card-title"><strong>{{ $pengajuanBulanIni }}</strong></h3>
                        <p style="font-size: 20px;font-weight: bold" class="card-text">Pengajuan Deposito Bulan :
                            {{ \Carbon\Carbon::parse($bulan)->locale('id')->isoFormat('MMMM Y') }}<br><small><a
                                    href="{{ route('pengajuan.bulanini') }}"
                                    class="text-white text-decoration-underline">Details</a></small></p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="icon-bg">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3 class="card-title"><strong>{{ $pengajuankemarin }} </strong></h3>
                        <p style="font-size: 20px;font-weight: bold" class="card-text">Pengajuan Deposito Kemarin :
                            {{ \Carbon\Carbon::yesterday()->locale('id')->isoFormat('dddd, D MMMM Y') }}<br><small><a
                                    href="{{ route('pengajuan.kemarin') }}"
                                    class="text-white text-decoration-underline">Details</a></small></p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="icon-bg">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3 class="card-title"><strong>{{ $pengajuanHariIni }} </strong></h3>
                        <p style="font-size: 20px;font-weight: bold" class="card-text">Pengajuan Deposito :
                            {{ \Carbon\Carbon::parse($today)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                            <br><small><a href="{{ route('pengajuan.hariini') }}"
                                    class="text-white text-decoration-underline">Details</a></small>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card bg-light text-white" style="background-color: #ffc107 !important;">
                    <div class="card-body">
                        <div class="icon-bg">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <h3 class="card-title"><strong>{{ $totalDepositoKredit }}</strong></h3>
                        <p style="font-size: 20px;font-weight: bold" class="card-text">Nasabah Deposito Yang Memiliki
                            Kredit<br>
                            <small><a href="{{ route('deposito.kredit') }}"
                                    class="text-white text-decoration-underline">Details</a></small>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card bg-light text-white" style="background-color: #17a2b8 !important;">
                    <div class="card-body">
                        <div class="icon-bg">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <h3 class="card-title"><strong>{{ $totalDepositoTabungan }}</strong></h3>
                        <p style="font-size: 20px;font-weight: bold" class="card-text">Nasabah Deposito Yang Memiliki
                            Tabungan<br>
                            <small><a href="{{ route('deposito.tabungan') }}"
                                    class="text-white text-decoration-underline">Details</a></small>
                        </p>
                    </div>
                </div>
            </div>



            <div class="col-12">
                <div class="card bg-light text-white" style="background-color: #0a7a02 !important;">
                    <div class="card-body">
                        <!-- Icon transparan di background kanan atas -->
                        <div class="icon-bg">
                            <i class="fa-solid fa-circle-dollar-to-slot"></i>
                        </div>
                        <h5 class="card-title"><strong>Total Nominal Deposito :</strong></h5>
                        <p style="font-size: 30px;font-weight: bold" class="card-text">
                            {{-- Total Nominal Deposito :<br> --}}
                            Rp {{ number_format($totalNominal, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

        </div>


        <!-- Footer -->
        <footer style="background-color: #b8bfb8" class="mt-4 text-center small text-muted">
            <div style="font-family: 'Times New Roman', Times, serif"><strong>Created By : Information Technology ©
                    2025
                    Bank DP Taspen.</strong> All rights reserved.</div>
            <div>Version 1.0.0</div>
        </footer>

    </div>

    <script>
        setTimeout(function() {
            // Tampilkan overlay sebelum reload
            document.getElementById('loadingOverlay').style.display = 'flex';

            setTimeout(function() {
                location.reload();
            }, 1000);
        }, 300000);

        function updateTimestamp() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            const formatter = new Intl.DateTimeFormat('id-ID', options);
            document.getElementById('timestamp').textContent = formatter.format(now);
        }

        setInterval(updateTimestamp, 1000);
        updateTimestamp();
    </script>

</body>

</html>
