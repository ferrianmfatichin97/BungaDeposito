<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyDeposito extends ChartWidget
{
    protected static ?string $heading = 'Total Nominal Deposito per Bulan';

    protected function getData(): array
{
    $data = DB::connection('mysql_REMOTE')
        ->table('data_deposito_master as d')
        ->selectRaw("DATE_FORMAT(d.dep_tgl_jthtempo, '%Y-%m') as bulan, SUM(d.dep_nilai_valuta) as total")
        ->where('d.dep_status', 1)
        ->groupBy('bulan')
        ->orderBy('bulan')
        ->get();

    $labels = $data->pluck('bulan')->map(function ($bulan) {
        return \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
    })->toArray();

    $totals = $data->pluck('total')->toArray();

    return [
        'datasets' => [
            [
                'label' => 'Total Nominal',
                'data' => $totals,
            ],
        ],
        'labels' => $labels,
    ];
}


    protected function getType(): string
    {
        return 'line';
    }
}
