<?php

namespace App\Filament\Resources\PayrollDepositoResource\Pages;

use Filament\Actions;
use App\Models\PayrollDeposito;
use App\Models\ProyeksiDeposito;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Exports\Models\Export;
use App\Filament\Exports\PayrollDepositoExporter;
use App\Exports\PayrollDepositoExport;
use App\Filament\Resources\PayrollDepositoResource;


class ListPayrollDepositos extends ListRecords
{
    protected static string $resource = PayrollDepositoResource::class;

    const DEFAULT_CURRENCY = 'IDR';
    const DEFAULT_EMAIL = 'bprtaspen@gmail.com';
    const DEFAULT_IBUOBU = 'IBU';
    const DEFAULT_REMARK1 = 'Budep ';
    const DEFAULT_REMARK2 = 'transactionRemark1';
    const DEFAULT_REMARK3 = 'transactionRemark2';
    const DEFAULT_ADJUST1 = 'transactionRemark3';
    const DEFAULT_ADJUST2 = 'valuePaymentDetails';
    const DEFAULT_ADJUST3 = 'N';
    const DEFAULT_ADJUST4 = 'N';
    const DEFAULT_ADJUST5 = 'extended payment detail';
    const DEFAULT_ADJUST6 = 'OUR';
    const DEFAULT_ADJUST7 = 'EPD';
    const DEFAULT_ADJUST8 = 'Y';
    const DEFAULT_ADJUST9 = '014';
    const DEFAULT_ADJUST10 = 'BPR0101309';
    const DEFAULT_ADJUST11 = '0';
    const DEFAULT_ADJUST12 = 'BANK MANDIRI TASPEN';
    const DEFAULT_ADJUST13 = '2144213178589';


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add')
                ->icon('heroicon-o-plus')
                ->color('primary'),

                // Actions\ExportAction::make()
                // ->exporter(PayrollDepositoExporter::class)
                // ->label('Export')
                // ->icon('heroicon-o-document-arrow-down')
                // ->color('primary'),
                // //->fileName(fn(Export $export): string => "Rekening Tujuan Transfer Pembayaran Bunga Deposito-{$export->getKey()}.xlsx"),

                Actions\Action::make('export1')
                ->label('Export')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->action(function () {
                    $currentDate = new \DateTime();
                    $month = $currentDate->format('m');
                    $year = $currentDate->format('Y');

                    $tanggalBayarGrouped = ProyeksiDeposito::select('tanggal_bayar')
                    ->groupBy('tanggal_bayar')
                    ->get();

                    $tanggalString = implode('_', $tanggalBayarGrouped->pluck('tanggal_bayar')->toArray());
                    $fileName = 'Rekening Tujuan Transfer Pembayaran Bunga Deposito_' . $tanggalString .'_'.$month.'_'.$year.'.xlsx';

                    return Excel::download(new PayrollDepositoExport(), $fileName);
                }),

            Actions\Action::make('generate')
                ->label('Generate Data')
                ->action(function () {
                    try {
                        DB::transaction(function () {
                            $getdata = ProyeksiDeposito::withRekeningTransfer()->get();
                            $insertData = [];

                            foreach ($getdata as $data) {
                                $rekening = $data->rekening;

                                $entry = [
                                    'norek_deposito' => $data['rek_deposito'],
                                    'nama_nasabah' => $data['nama_nasabah'],
                                    'tanggal_bayar' => $data['tanggal_bayar'],
                                    'norek_tujuan' => $rekening->norek_tujuan ?? 0,
                                    'bank_tujuan' => $rekening->bank_tujuan ?? 0,
                                    'kode_bank' => $rekening->kode_bank ?? null,
                                    'nama_rekening' => $rekening->nama_rekening ?? 0,
                                    'nominal' => $data['total_bayar'],
                                    'jatuh_tempo' => $data['jatuh_tempo'],
                                    'status' => $data['status'],
                                    'currency' => self::DEFAULT_CURRENCY,
                                    'emailcorporate' => self::DEFAULT_EMAIL,
                                    'ibuobu' => self::DEFAULT_IBUOBU,
                                    'remark1' => self::DEFAULT_REMARK1,
                                    'remark2' => self::DEFAULT_REMARK2,
                                    'remark3' => self::DEFAULT_REMARK3,
                                    'adjust1' => self::DEFAULT_ADJUST1,
                                    'adjust2' => self::DEFAULT_ADJUST2,
                                    'adjust3' => self::DEFAULT_ADJUST3,
                                    'adjust4' => self::DEFAULT_ADJUST4,
                                    'adjust5' => self::DEFAULT_ADJUST5,
                                    'adjust6' => self::DEFAULT_ADJUST6,
                                    'adjust7' => self::DEFAULT_ADJUST7,
                                    'adjust8' => self::DEFAULT_ADJUST8,
                                    'adjust9' => self::DEFAULT_ADJUST9,
                                    'adjust10' => self::DEFAULT_ADJUST10,
                                    'adjust11' => self::DEFAULT_ADJUST11,
                                    'adjust12' => self::DEFAULT_ADJUST12,
                                    'adjust13' => self::DEFAULT_ADJUST13,
                                ];

                                $insertData[] = $entry;
                            }

                            //dd($insertData);

                            foreach ($insertData as $data) {
                                Log::info('Memeriksa norek_tujuan:', ['norek_tujuan' => $data['norek_tujuan']]);

                                $result = PayrollDeposito::create($data);

                                if ($result) {
                                    Log::info('Data Payroll Deposito Berhasil Di Generate', $data);
                                }
                            }

                            Notification::make()
                                ->title('Generate Data Berhasil')
                                ->success()
                                ->send();
                        });
                    } catch (\Exception $e) {
                        Log::error('Gagal Generate Data: ' . $e->getMessage());
                        Notification::make()
                            ->title('Gagal Generate Data')
                            ->danger()
                            ->send();
                    }
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
        $tableName = 'payroll_depositos';

        DB::table($tableName)->truncate();

        Notification::make()
            ->title('Data Berhasil Di Hapus')
            ->success()
            ->send();
    }
}
