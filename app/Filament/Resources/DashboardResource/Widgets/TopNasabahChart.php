<?php

namespace App\Filament\Resources\DashboardResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopNasabahChart extends ChartWidget
{
   protected static ?string $heading = 'Top 10 Nasabah Berdasarkan Nominal Deposito';

    protected function getData(): array
    {
        $data = DB::connection('mysql_REMOTE')
            ->table('data_deposito_master as d')
            ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
            ->selectRaw('n.nasabah_nama_lengkap as nama_nasabah, SUM(d.dep_nilai_valuta) as total')
            ->groupBy('n.nasabah_nama_lengkap')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $labels = $data->pluck('nama_nasabah')->toArray();
        $totals = $data->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Nominal',
                    'data' => $totals,
                    'backgroundColor' => 'rgb(75, 192, 192)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
