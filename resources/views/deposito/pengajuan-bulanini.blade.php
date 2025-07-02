<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pengajuan Bulan Ini</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>

<body>
    <div class="container py-4">
        <!-- Header dengan logo kiri atas -->
        <nav class="navbar navbar-expand-sm bg-light navbar-light fixed-top shadow-sm">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <!-- Kiri: Logo dan Judul -->
                <a class="navbar-brand d-flex align-items-center gap-3 mb-0">
                    <img src="{{ asset('logo.png') }}" alt="Logo" height="50">
                    <span class="fw-bold">Dashboard Deposito</span>
                </a>

                <a href="{{ route('custom.dashboard') }}" class="btn btn-outline-primary">
                    Ke Beranda
                </a>
            </div>
        </nav>

        <div class="pt-5 mt-4"></div>

        <!-- Judul Dashboard -->
        <div class="container">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body text-center py-3">
                    <h2 class="mb-0">Pengajuan Bulan Ini</h2>
                </div>
            </div>
        </div>
        <div class="container py-4">
            <div class="card">
                <div class="card-body">
                    @php
                        $kantorMap = [
                            // '00' => 'KP. Manajemen',
                            '01' => 'KP. Operasional',
                            '02' => 'KC. Bogor',
                            '03' => 'KC. Depok',
                            '04' => 'KC. Tangerang',
                            '05' => 'KC. Jakarta Timur',
                            '06' => 'KC. Karawang',
                            '07' => 'KC. Cikarang',
                            '08' => 'KC. Purwokerto',
                            // '09' => 'KC. Cirebon',
                        ];

                        $pengajuanPerKantor = [];

                        foreach ($dataDeposito as $item) {
                            $kode = str_pad($item->v_kantor2, 2, '0', STR_PAD_LEFT);
                            if (!isset($pengajuanPerKantor[$kode])) {
                                $pengajuanPerKantor[$kode] = [
                                    'jumlah' => 0,
                                    'nominal' => 0,
                                ];
                            }
                            $pengajuanPerKantor[$kode]['jumlah']++;
                            $pengajuanPerKantor[$kode]['nominal'] += $item->saldoawal;
                        }
                    @endphp

                    <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
                         @foreach ($kantorMap as $kode => $namaKantor)
                        @php
                            $jumlah = $pengajuanPerKantor[$kode]['jumlah'] ?? 0;
                            $nominal = $pengajuanPerKantor[$kode]['nominal'] ?? 0;
                        @endphp
                        <div class="col">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $namaKantor }}</h5>
                                    <p class="card-text mb-1">
                                        Total Pengajuan: <strong>{{ $jumlah }}</strong>
                                    </p>
                                    <p class="card-text mb-0">
                                        Total Nominal: <strong>Rp {{ number_format($nominal, 0, ',', '.') }}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>

                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <table id="depositoTable" class="table table-bordered table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Rek Deposito</th>
                                <th>Nama Nasabah</th>
                                <th>CIF</th>
                                <th>NIK</th>
                                <th>Nominal</th>
                                <th>Tgl Buka</th>
                                <th>Jatuh Tempo</th>
                                <th>Suku Bunga</th>
                                <th>Kantor Cabang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $kantorMap = [
                                    '00' => 'KP. Manajemen',
                                    '01' => 'KP. Operasional',
                                    '02' => 'KC. Bogor',
                                    '03' => 'KC. Depok',
                                    '04' => 'KC. Tangerang',
                                    '05' => 'KC. Jakarta Timur',
                                    '06' => 'KC. Karawang',
                                    '07' => 'KC. Cabangbungin',
                                    '08' => 'KC. Purwokerto',
                                    '09' => 'KC. Cirebon',
                                ];
                            @endphp
                            @foreach ($dataDeposito as $i => $d)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $d->v_rekening1 }}</td>
                                    <td>{{ $d->v_nama1 }}</td>
                                    <td>{{ $d->v_cif1 }}</td>
                                    <td>{{ $d->v_nomor_ktp }}</td>
                                    <td>Rp {{ number_format($d->saldoawal, 0, ',', '.') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($d->v_tglbuka)->format('d-m-Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($d->v_tgljtempo)->format('d-m-Y') }}</td>
                                    <td>{{ $d->v_sukubunga }}%</td>
                                    <td>
                                        {{ $kantorMap[$d->v_kantor2] ?? 'Tidak Dikenal' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer style="background-color: #b8bfb8" class="mt-4 text-center small text-muted py-2 rounded">
            <div style="font-family: 'Times New Roman', Times, serif">
                <strong>Created By : Information Technology © 2025 Bank DP Taspen.</strong> All rights reserved.
            </div>
            <div>Version 1.0.0</div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#depositoTable').DataTable({
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Berikutnya"
                    },
                    zeroRecords: "Data tidak ditemukan",
                    infoEmpty: "Tidak ada data tersedia",
                    infoFiltered: "(difilter dari total _MAX_ data)"
                },
                order: [
                    [0, 'asc']
                ]
            });
        });
    </script>

</body>

</html>
