<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>List Nasabah Deposito</title>
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
                    <h2 class="mb-0">List Nasabah Deposito</h2>
                </div>
            </div>
        </div>

        <!-- Tabel Data -->
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
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                    @if ($d->v_abp == 1)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
