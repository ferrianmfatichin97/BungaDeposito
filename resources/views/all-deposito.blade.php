<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Deposito Nasabah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        header {
            border-bottom: 2px solid #dee2e6;
            padding: 1rem 0;
            margin-bottom: 1rem;
        }

        .logo {
            height: 50px;
        }

        .table thead {
            background-color: #0d6efd;
            color: white;
        }

        footer {
            margin-top: 3rem;
            font-size: 0.9rem;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>

<body>

    <div class="container py-3">

        <!-- Header -->
        <header class="d-flex align-items-center justify-content-between border-bottom mb-4 pb-2">
            <div class="d-flex align-items-center">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="logo me-3">
            </div>
            <div class="flex-grow-1 text-center">
                <h4 class="fw-bold mb-0">Data Nasabah Deposito</h4>
            </div>
        </header>


        <!-- Filter & Search Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('depo.view') }}" class="mb-4">
                    <div class="row align-items-end g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Dari Tanggal Jatuh Tempo</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Sampai Tanggal Jatuh Tempo</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Pencarian</label>
                            <input type="text" name="search" id="search" class="form-control"
                                value="{{ request('search') }}" placeholder="Nama / CIF / Rekening">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table id="depoTable" class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th>CIF</th>
                            <th>Nama</th>
                            <th>Rekening</th>
                            <th>Nominal</th>
                            <th>Tanggal Buka</th>
                            <th>Jatuh Tempo</th>
                            <th>Suku Bunga</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $d)
                            <tr>
                                <td>{{ $d->cif_nasabah }}</td>
                                <td>{{ $d->nama_nasabah }}</td>
                                <td>{{ $d->rekening_deposito }}</td>
                                <td>Rp {{ number_format($d->nominal_deposito, 0, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($d->tgl_buka_deposito)->format('d-m-Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($d->tgl_jatuh_tempo_deposito)->format('d-m-Y') }}</td>
                                <td>{{ $d->suku_bunga_deposito }}%</td>
                                <td>
                                    @php
                                        $statusMap = [
                                            0 => 'None',
                                            1 => 'Aktif',
                                            2 => 'Blokir',
                                            3 => 'Tutup',
                                            9 => 'Delete',
                                        ];
                                    @endphp
                                    {{ $statusMap[$d->status_rekening] ?? 'Unknown' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Data tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        <!-- Footer -->
        <footer>
            <div class="mt-4">© {{ date('Y') }} Bank DP Taspen. All rights reserved.</div>
            <div><small>Versi 1.0 | Dibuat oleh IT Dept</small></div>
        </footer>

    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#depoTable').DataTable({
                paging: true,
                searching: false,
                ordering: true,
                info: true
            });
        });
    </script>

</body>

</html>
