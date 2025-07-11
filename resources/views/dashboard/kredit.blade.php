<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Kredit</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div class="container py-3">

        <!-- Logo kiri atas -->
        <div class="d-flex align-items-center mb-3">
            <img src="{{ asset('logo.png') }}" alt="Logo" height="50">
        </div>

        <!-- Judul Dashboard -->
        <div style="font-size: 50px" class="dashboard-title text-center mb-4">DASHBOARD KREDIT</div>

        <!-- Cards -->
        <div class="row mt-3 g-3">

            <!-- Card Full Width -->
            <div class="col-12">
                <div class="card bg-light text-white" style="background-color: #aeb0b0 !important;">
                    <!-- Icon transparan di background kanan atas -->
                    <div class="icon-bg">

                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><strong>{{ $totalKredit }}</strong></h3>
                        <p style="font-size: 20px" class="card-text"><strong>Nasabah Kredit Aktif</strong><br>
                            <small><a href="{{ route('deposito.list') }}" target="_blank"
                                    class="text-white text-decoration-underline">Details</a></small>
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2-Column Cards -->

            <div class="col-12 col-md-6">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="icon-bg">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3 class="card-title"><strong>{{ $pengajuanHariIni }} </strong></h3>
                        <p style="font-size: 20px;font-weight: bold" class="card-text">Pengajuan Kredit :
                            {{ \Carbon\Carbon::parse($today)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                        </p>
                        <p style="font-size: 18px">Nominal: <strong>Rp
                                {{ number_format($nominalHariIni, 0, ',', '.') }}</strong></p>
                        <small><a href="" target="_blank"
                                class="text-white text-decoration-underline">Details</a></small>
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
                        <p style="font-size: 20px;font-weight: bold" class="card-text">Pengajuan Kredit Bulan :
                            {{ \Carbon\Carbon::parse($bulan)->locale('id')->isoFormat('MMMM Y') }}
                        </p>
                        <p style="font-size: 18px">Nominal: <strong>Rp
                                {{ number_format($nominalBulanIni, 0, ',', '.') }}</strong></p>
                        <small><a href="" target="_blank"
                                class="text-white text-decoration-underline">Details</a></small>
                    </div>
                </div>
            </div>

            {{-- <div class="col-12 col-md-6">
                <div class="card bg-light text-white" style="background-color: #ffc107 !important;">
                    <div class="card-body">
                        <div class="icon-bg">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <h3 class="card-title"><strong>{{ $totalNasabahDenganKredit }}</strong></h3>
                        <p style="font-size: 20px;font-weight: bold" class="card-text">Nasabah Kredit Yang Memiliki
                            Deposito<br>
                            <small><a href="{{ route('deposito.kredit') }}"
                                    class="text-white text-decoration-underline" target="_blank">Details</a></small>
                        </p>
                    </div>
                </div>
            </div> --}}

            {{-- <div class="col-12 col-md-6">
                <div class="card bg-light text-white" style="background-color: #17a2b8 !important;">
                    <div class="card-body">
                        <div class="icon-bg">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <h3 class="card-title"><strong>total</strong></h3>
                        <p style="font-size: 20px;font-weight: bold" class="card-text">Nasabah Kredit Yang Memiliki
                            Tabungan<br>
                            <small><a href="{{ route('deposito.tabungan') }}" target="_blank"
                                    class="text-white text-decoration-underline">Details</a></small>
                        </p>
                    </div>
                </div>
            </div> --}}

            <div class="col-12">
                <div class="card bg-light text-white" style="background-color: #0a7a02 !important;">
                    <div class="card-body">
                        <!-- Icon transparan di background kanan atas -->
                        <div class="icon-bg">
                            <i class="fa-solid fa-circle-dollar-to-slot"></i>
                        </div>
                        <h5 class="card-title"><strong>Total Nominal Kredit :</strong></h5>
                        <p style="font-size: 30px;font-weight: bold" class="card-text">
                            Rp {{ number_format($totalNominal, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

        </div>


        <!-- Footer -->
        <footer style="background-color: #b8bfb8" class="mt-4 text-center small text-muted">
            <div style="font-family: 'Times New Roman', Times, serif"><strong>Created By : Information Technology © 2025
                    Bank DP Taspen.</strong> All rights reserved.</div>
            <div>Version 1.0.0</div>
        </footer>

    </div>

</body>

</html>
