@extends('layouts.app') {{-- layout dengan header/footer dan logo kiri atas --}}

@section('title', 'Dashboard Deposito')

@section('content')
    <div class="container py-4">
        <h1 class="mb-4">Dashboard Deposito</h1>

        <div class="row mb-4">
            {{-- Cards --}}
            @php
                $cardData = [
                    ['title' => 'Total Nasabah Deposito', 'value' => $totalDeposito, 'link' => route('deposito.list')],
                    [
                        'title' => 'Nasabah Deposito + Kredit',
                        'value' => $totalDepositoKredit,
                        'link' => route('deposito.kredit'),
                    ],
                    [
                        'title' => 'Nasabah Deposito + Tabungan',
                        'value' => $totalDepositoTabungan,
                        'link' => route('deposito.tabungan'),
                    ],
                    [
                        'title' => 'Pengajuan Hari Ini',
                        'value' => $pengajuanHariIni,
                        'link' => route('deposito.pengajuan.hariini'),
                    ],
                    [
                        'title' => 'Pengajuan Bulan Ini',
                        'value' => $pengajuanBulanIni,
                        'link' => route('deposito.pengajuan.bulanini'),
                    ],
                    ['title' => 'Total Nominal Deposito', 'value' => 'Rp ' . number_format($totalNominal, 0, ',', '.')],
                ];
            @endphp

            @foreach ($cardData as $card)
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <h6 class="card-title">{{ $card['title'] }}</h6>
                            <h3 class="card-text">{{ $card['value'] }}</h3>
                            @if (isset($card['link']))
                                <a href="{{ $card['link'] }}" target="_blank" class="stretched-link"></a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Optionally, use DataTables or other JS enhancements
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('depositoTable');
            // Optionally initialize DataTables here
        });
    </script>
@endpush
