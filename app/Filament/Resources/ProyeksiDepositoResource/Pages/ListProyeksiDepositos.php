<?php

namespace App\Filament\Resources\ProyeksiDepositoResource\Pages;

use App\Filament\Exports\ProyeksiDepositoExporter;
use App\Filament\Resources\ProyeksiDepositoResource;
use App\Models\ProyeksiDeposito;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Exports\Models\Export;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;

class ListProyeksiDepositos extends ListRecords
{
    protected static string $resource = ProyeksiDepositoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add')
                ->icon('heroicon-o-plus')
                ->color('primary'),

            Actions\ExportAction::make()
                ->exporter(ProyeksiDepositoExporter::class)
                ->label('Export')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->fileName(fn(Export $export): string => "Proyeksi Deposito-(Data tidak lengkap){$export->getKey()}.xlsx"),

            Actions\Action::make('proyeksiDeposito')
                ->label('Tarik Proyeksi Deposito')
                ->form([
                    Grid::make(2)->schema([
                        DatePicker::make('tanggal_awal')
                            ->label('Tanggal Awal')
                            ->required(),
            
                        DatePicker::make('tanggal_akhir')
                            ->label('Tanggal Akhir')
                            ->required(),
                    ]),
                ])
                ->action(function (array $data): void {
                    $tanggalAwal = $data['tanggal_awal'];
                    $tanggalAkhir = $data['tanggal_akhir'];
            
                    $daysToCheck = range(date('d', strtotime($tanggalAwal)), date('d', strtotime($tanggalAkhir)));
                    //$daysToCheck = [10];
                    
                    // dd([
                    //     'tanggal_awal' => $tanggalAwal,
                    //     'tanggal_akhir' => $tanggalAkhir,
                    //     'daysToCheck' => $daysToCheck,
                    // ]);

                    $deposits = DB::connection('mysql_REMOTE')->table('data_deposito_master as d')
                        ->join('data_nasabah_master as n', 'd.dep_nasabah', '=', 'n.nasabah_id')
                        ->join('data_deposito_pelengkap as p', 'd.dep_rekening', '=', 'p.pelengkap_rekening')
                        ->select(
                            'd.dep_rekening AS rek_deposito',
                            'n.nasabah_nama_lengkap AS nama_nasabah',
                            'd.dep_jkw AS jangka_waktu',
                            'd.dep_bunga_persen AS nilai_bunga',
                            'd.dep_nilai_valuta AS saldo_valuta_awal',
                            'd.dep_tabungan AS dep_tabungan',
                            DB::raw('(d.dep_nilai_valuta * d.dep_bunga_persen / 100) AS bunga'),
                            DB::raw('ROUND((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12)) AS total_bunga'),
                            DB::raw('ROUND(((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12) * 0.2)) AS total_pajak'),
                            DB::raw('ROUND((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12)) - ROUND(((d.dep_nilai_valuta * d.dep_bunga_persen / 100 / 12) * 0.2)) AS total_bayar'),
                            'd.dep_tujuanpeng AS tujuan_penggunaan',
                            DB::raw('IFNULL(LPAD(DAY(d.dep_tgl_jthtempo), 2, "0"), "01") AS tanggal_bayar'),
                            'd.dep_tgl_jthtempo AS jatuh_tempo',
                            'd.dep_status AS status',
                            'p.pelengkap_pajak_bebas AS pelengkap_pajak_bebas',
                            'd.dep_abp AS dep_abp'
                        )
                        ->where('d.dep_status', 1)
                        ->where('d.dep_tabungan', '')
                        ->whereIn(DB::raw('DAY(d.dep_tgl_jthtempo)'), $daysToCheck)
                        ->get();

                        //dd($deposits);
            
                    
                    foreach ($deposits as $deposit) {
                        $total_bayar = $deposit->total_bayar;
                        $total_bunga = $deposit->total_bunga;
            
                        if ($deposit->pelengkap_pajak_bebas == 1) {
                            $total_bayar = $total_bunga;
                            $total_bunga = 0;
                        }
            
                        ProyeksiDeposito::create([
                            'rek_deposito' => $deposit->rek_deposito,
                            'nama_nasabah' => $deposit->nama_nasabah,
                            'jangka_waktu' => $deposit->jangka_waktu,
                            'nilai_bunga' => $deposit->nilai_bunga,
                            'saldo_valuta_awal' => $deposit->saldo_valuta_awal,
                            'bunga' => $deposit->bunga,
                            'total_bunga' => $total_bunga,
                            'total_pajak' => $deposit->total_pajak,
                            'total_bayar' => $total_bayar,
                            'tujuan_penggunaan' => $deposit->tujuan_penggunaan,
                            'tanggal_bayar' => $deposit->tanggal_bayar,
                            'jatuh_tempo' => $deposit->jatuh_tempo,
                            'status' => $deposit->status,
                            'dep_abp' => $deposit->dep_abp,
                        ]);
                    }
            
                    Notification::make()
                        ->title('Download Proyeksi Deposito executed successfully')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('Clear Data')
                ->action(function () {
                    $this->truncateTable();
                })
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->color('danger'),
        ];
    }

    protected function truncateTable()
    {
        $tableName = 'proyeksi_depositos';

        DB::table($tableName)->truncate();

        session()->flash('message', "Table '{$tableName}' has been Deleted successfully.");
    }
}
