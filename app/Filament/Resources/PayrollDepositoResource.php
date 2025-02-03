<?php

namespace App\Filament\Resources;

use App\Events\UserActivityLogged;
use App\Exports\PayrollBIFASTBRIExport;
use App\Exports\PayrollBNIExport;
use App\Exports\PayrollBRIExport;
use App\Exports\PayrollMandiriExport;
use App\Filament\Resources\PayrollDepositoResource\Pages;
use App\Models\PayrollDeposito;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
                    'AKTIF' => 'AKTIF',
                    'TIDAK AKTIF' => 'TIDAK AKTIF',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Tabel Payroll Deposito')
            ->columns([
                TextColumn::make('norek_deposito')->label('No.Referensi')->searchable(),
                TextColumn::make('nama_rekening'),
                TextColumn::make('norek_tujuan')->searchable()->copyable()->copyMessage('Berhasil Di Copy'),
                TextColumn::make('bank_tujuan'),
                TextColumn::make('kode_bank'),
                TextColumn::make('nominal')
                    ->formatStateUsing(fn(PayrollDeposito $record): string => 'Rp ' . number_format($record->nominal, 0, '.', '.'))
                    ->summarize(Sum::make()->label('Total')->money('IDR')),
                TextColumn::make('jatuh_tempo')
                    ->alignment(Alignment::Center),
                TextColumn::make('ibuobu'),
                IconColumn::make('status')
                    ->alignment(Alignment::Center)
                    ->icon(fn(string $state): string => match ($state) {
                        "AKTIF" => 'heroicon-o-check-circle',
                        "TIDAK AKTIF" => 'heroicon-o-x-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'AKTIF' => 'success',
                        'TIDAK AKTIF' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('bank_tujuan')->multiple()->label('Bank Tujuan')->options([
                    'ARTHA GRAHA' => 'ARTHA GRAHA',
                    'BANK ACEH SYARIAH' => 'BANK ACEH SYARIAH',
                    'BANK BANTEN' => 'BANK BANTEN',
                    'BANK BENGKULU' => 'BANK BENGKULU',
                    'BANK JAGO' => 'BANK JAGO',
                    'BANK JATENG' => 'BANK JATENG',
                    'BANK JATIM' => 'BANK JATIM',
                    'BANK LAMPUNG' => 'BANK LAMPUNG',
                    'BANK SULSELBAR' => 'BANK SULSELBAR',
                    'BANK SULSELBAR SYARIAH' => 'BANK SULSELBAR SYARIAH',
                    'BANK SULTRA' => 'BANK SULTRA',
                    'BANK SULUT' => 'BANK SULUT',
                    'BANK SULUTGO' => 'BANK SULUTGO',
                    'BCA' => 'BCA',
                    'BCA DIGITAL' => 'BCA DIGITAL',
                    'BCA SYARIAH' => 'BCA SYARIAH',
                    'BJB' => 'BJB',
                    'BJB SYARIAH' => 'BJB SYARIAH',
                    'BNI' => 'BNI',
                    'BRI' => 'BRI',
                    'BSI' => 'BSI',
                    'BTN' => 'BTN',
                    'BTN SYARIAH' => 'BTN SYARIAH',
                    'BTPN' => 'BTPN',
                    'BUKOPIN' => 'BUKOPIN',
                    'BUMI ARTA' => 'BUMI ARTA',
                    'BWS' => 'BWS',
                    'CIMB' => 'CIMB NIAGA',
                    'DKI' => 'DKI',
                    'DKI SYARIAH' => 'DKI SYARIAH',
                    'MANDIRI' => 'MANDIRI',
                    'MANTAP' => 'MANTAP',
                    'MUAMALAT' => 'MUAMALAT',
                    'PANIN BANK' => 'PANIN BANK',
                ]),
                SelectFilter::make('jatuh_tempo')->options(array_combine(range(1, 31), range(1, 31))),
                SelectFilter::make('status')->label(' Status')->options([
                    'AKTIF' => 'AKTIF',
                    'TIDAK AKTIF' => 'Tidak Aktif',
                ]),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                    ->before(function () {
                            Event::dispatch(new UserActivityLogged('update', Auth::id(), 'PayrollDeposito'));
                            log::info('ini log aftersave' . Auth::id());
                        }),
                    DeleteAction::make()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                Event::dispatch(new UserActivityLogged('delete', auth::id(), 'PayrollDeposito'));
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
                            $date = date('d-m-Y');
                            $fileName = 'Budep_Mandiri_' . $date . '.csv';
                            return Excel::download(new PayrollMandiriExport($records), $fileName);
                        }),
                    Tables\Actions\BulkAction::make('export1')
                        ->label('Format BNI')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records) {
                            $date = date('Ymd');
                            $fileName = 'Budep_BNI_' . $date . '.csv';
                            return Excel::download(new PayrollBNIExport($records), $fileName);
                        }),
                    Tables\Actions\BulkAction::make('export2')
                        ->label('Format BRI')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records) {
                            $date = date('Ymd');
                            $fileName = 'Budep_BRI_' . $date . '.csv';
                            return Excel::download(new PayrollBRIExport($records), $fileName);
                        }),
                    Tables\Actions\BulkAction::make('export3')
                        ->label('Format BI-Fast BRI')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records) {
                            $date = date('Ymd');
                            $fileName = 'Budep_BI-Fast BRI_' . $date . '.csv';
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrollDepositos::route('/'),
            'create' => Pages\CreatePayrollDeposito::route('/create'),
            'edit' => Pages\EditPayrollDeposito::route('/{record}/edit'),
        ];
    }
}
