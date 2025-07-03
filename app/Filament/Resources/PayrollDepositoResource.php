<?php

namespace App\Filament\Resources;

use App\Events\UserActivityLogged;
use App\Exports\PayrollBIFASTBRIExport;
use App\Exports\PayrollBNIExport;
use App\Exports\PayrollBRIExport;
use App\Exports\PayrollMandiriExport;
use App\Filament\Resources\PayrollDepositoResource\Pages;
use App\Models\PayrollDeposito;
use Carbon\Carbon;
use DeepCopy\Filter\Filter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class PayrollDepositoResource extends Resource
{
    protected static ?string $model = PayrollDeposito::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('norek_deposito')->required()->label('No.Referensi'),
            Forms\Components\TextInput::make('nama_nasabah')->required(),
            Forms\Components\TextInput::make('norek_tujuan')->required(),
            Forms\Components\TextInput::make('bank_tujuan')->required(),
            Forms\Components\TextInput::make('nama_rekening')->required(),
            Forms\Components\TextInput::make('nominal')->required(),
            Forms\Components\TextInput::make('jatuh_tempo')->required(),
            Forms\Components\Select::make('status')
                ->options([
                    '1' => 'AKTIF',
                    '2' => 'TIDAK AKTIF',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Tabel Payroll Deposito')
            ->striped()
            ->columns([
                TextColumn::make('norek_deposito')->label('No.Referensi')->searchable()->copyable()->copyMessage('Berhasil Di Copy'),
                TextColumn::make('nama_nasabah'),
                TextColumn::make('norek_tujuan')->searchable()->copyable()->copyMessage('Berhasil Di Copy'),
                TextColumn::make('bank_tujuan'),
                TextColumn::make('total_bunga')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn(PayrollDeposito $record): string => 'Rp ' . number_format($record->total_bunga, 0, '.', '.'))
                    ->summarize(Sum::make()->label('Total')->money('IDR')),
                TextColumn::make('nominal')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn(PayrollDeposito $record): string => 'Rp ' . number_format($record->nominal, 0, '.', '.'))
                    ->summarize(Sum::make()->label('Total')->money('IDR')),
                TextColumn::make('tanggal_bayar')
                    ->label('Tanggal Bayar')
                    ->alignment(Alignment::Center),
                TextColumn::make('nama_rekening')
                    ->label('Nama Rekening'),
                TextColumn::make('jatuh_tempo')
                    ->date()
                    ->sortable()
                    ->alignment(Alignment::Center),

                IconColumn::make('status')
                    ->alignment(Alignment::Center)
                    ->icon(fn(string $state): string => match ($state) {
                        "1" => 'heroicon-o-check-circle',
                        "2" => 'heroicon-o-x-circle',
                        "3" => 'heroicon-o-x-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        '1' => 'success',
                        '2' => 'danger',
                        '3' => 'gray',
                    }),
                TextColumn::make('dep_abp')
                    ->label('Kode Abp'),
                TextColumn::make('saldo_valuta_awal')
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn(PayrollDeposito $record): string => 'Rp ' . number_format($record->saldo_valuta_awal, 0, '.', '.'))
                    ->summarize(Sum::make()->label('Total')->money('IDR')),
            ])
            ->defaultSort('tanggal_bayar')
            ->filters([
                SelectFilter::make('bank_tujuan')
                    ->label('Bank Tujuan')
                    ->multiple()
                    ->searchable()
                    ->options(PayrollDeposito::distinct()->pluck('bank_tujuan', 'bank_tujuan')->filter(fn($value) => !is_null($value))),

                Tables\Filters\Filter::make('bank_tujuan2')
                    ->label('BI Fast')
                    ->query(fn(Builder $query): Builder => $query->whereNotIn('bank_tujuan', ['BRI', 'MANDIRI'])),

                SelectFilter::make('tanggal_bayar')
                    ->label('Tanggal Bayar')
                    ->multiple()
                    ->searchable()
                    ->options(PayrollDeposito::distinct()->pluck('tanggal_bayar', 'tanggal_bayar')->filter(fn($value) => !is_null($value))),

            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->after(function (Collection $records) {
                            foreach ($records as $record) {
                                Event::dispatch(new UserActivityLogged('Delete', auth::id(), 'PayrollDeposito'));
                                $record->delete();
                            }
                            Notification::make()
                                ->title('Records deleted successfully!')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('Update IbuObu')
                        ->icon('heroicon-m-pencil-square')
                        ->form([
                            Forms\Components\Select::make('ibuobu_value')
                                ->label('Select IbuObu Value')
                                ->options([
                                    'IBU' => 'IBU',
                                    'OBU' => 'OBU',
                                    ''    => 'Kosongkan',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records) {
                            foreach ($records as $record) {
                                $record->update(['ibuobu' => $data['ibuobu_value']]);
                            }
                            Notification::make()
                                ->title('IbuObu updated successfully!')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Update IbuObu'),
                    Tables\Actions\BulkAction::make('Update Remark')
                        ->icon('heroicon-m-pencil-square')
                        ->form([
                            Forms\Components\TextInput::make('remark1_value')
                                ->label('Enter Remark Value')
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records) {
                            $updatedCount = 0;
                            $failedCount = 0;

                            foreach ($records as $record) {
                                try {
                                    $record->update(['remark1' => $data['remark1_value']]);
                                    $updatedCount++;
                                } catch (\Exception $e) {
                                    $failedCount++;
                                }
                            }

                            Notification::make()
                                ->title('Update Complete')
                                ->success()
                                ->body("Successfully updated {$updatedCount} records. Failed to update {$failedCount} records.")
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Update Remark'),

                    Tables\Actions\BulkAction::make('export')
                        ->label('Format Mandiri')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('primary')
                        ->action(function (Collection $records) {

                            $tanggal_bayar = $records->pluck('tanggal_bayar')->first();

                            $bulan = date('m');
                            $tahun = date('Y');

                            $tanggal = $tanggal_bayar . '-' . $bulan . '-' . $tahun;

                            $fileName = 'Budep_Mandiri_' . $tanggal . '.csv';
                            // dd([
                            //     'records' => $records,
                            //     'filename' => $fileName,
                            // ]);
                            return Excel::download(new PayrollMandiriExport($records), $fileName);
                        }),
                    Tables\Actions\BulkAction::make('export1')
                        ->label('Format BNI')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records) {
                            $tanggal_bayar = $records->pluck('tanggal_bayar')->first();

                            $bulan = date('m');
                            $tahun = date('Y');

                            $tanggal = $tanggal_bayar . '-' . $bulan . '-' . $tahun;
                            $fileName = 'Budep_BNI_' . $tanggal . '.csv';

                            return Excel::download(new PayrollBNIExport($records), $fileName);
                        }),
                    Tables\Actions\BulkAction::make('export2')
                        ->label('Format BRI')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records) {
                            $tanggal_bayar = $records->pluck('tanggal_bayar')->first();

                            $bulan = date('m');
                            $tahun = date('Y');

                            $tanggal = $tanggal_bayar . '-' . $bulan . '-' . $tahun;
                            $fileName = 'Budep_BRI_' . $tanggal . '.csv';
                            return Excel::download(new PayrollBRIExport($records), $fileName);
                        }),
                    Tables\Actions\BulkAction::make('export3')
                        ->label('Format BI-Fast BRI')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records) {
                            $tanggal_bayar = $records->pluck('tanggal_bayar')->first();

                            $bulan = date('m');
                            $tahun = date('Y');

                            $tanggal = $tanggal_bayar . '_' . $bulan . '_' . $tahun;
                            $fileName = 'Budep_BIFast BRI_' . $tanggal . '.csv';
                            return Excel::download(new PayrollBIFASTBRIExport($records), $fileName);
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    protected function afterCreate($record): void
    {
        $this->checkAndUpdateStatus($record);
    }

    protected function afterUpdate($record): void
    {
        $this->checkAndUpdateStatus($record);
    }

    protected function checkAndUpdateStatus($record): void
    {
        $deposito = $record->deposito;

        if ($deposito && $deposito->dep_tgl_jthtempo->isToday()) {

            $record->status = 'Tidak Aktif';
            $record->save();
        }
        log::info('Berhasil Save' . $record);
    }

    // public static function canViewAny(): bool
    // {
    //     return auth()->user()->isUser();
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrollDepositos::route('/'),
            'create' => Pages\CreatePayrollDeposito::route('/create'),
            'edit' => Pages\EditPayrollDeposito::route('/{record}/edit'),
        ];
    }
}
