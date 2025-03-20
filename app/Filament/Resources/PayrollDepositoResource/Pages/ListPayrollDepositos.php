<?php

namespace App\Filament\Resources\PayrollDepositoResource\Pages;

use App\Filament\Exports\PayrollDepositoExporter;
use Filament\Actions;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\PayrollDepositoResource;
use App\Models\ProyeksiDeposito;
use App\Models\PayrollDeposito;
use Filament\Actions\Exports\Models\Export;

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

            Actions\ExportAction::make()
                ->exporter(PayrollDepositoExporter::class)
                ->label('Export')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->fileName(fn(Export $export): string => "Rek_Tujuan_Transfer_Pembayaran_Budep-{$export->getKey()}.csv"),

                Actions\Action::make('generate')
                ->label('Generate Data')
                ->action(function () {
                    try {
                        DB::transaction(function () {
                            $getdata = ProyeksiDeposito::withRekeningTransfer()->get();
                            $insertData = [];

                            foreach ($getdata as $data) {
                                $rekening = $data->rekening;

                                $insertData[] = [
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
                            }

                            foreach ($insertData as $data) {
                                if ($data['norek_tujuan'] !== 0 && $data['norek_tujuan'] !== null) {
                                    PayrollDeposito::updateOrCreate(
                                        ['norek_deposito' => $data['norek_deposito']],
                                        $data
                                    );
                                }
                            }

                            session()->flash('message', 'Payroll Deposito records generated successfully.');
                        });
                    } catch (\Exception $e) {
                        session()->flash('error', 'An error occurred while generating records: ' . $e->getMessage());
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

        session()->flash('message', "Table '{$tableName}' has been deleted successfully.");
    }
}
