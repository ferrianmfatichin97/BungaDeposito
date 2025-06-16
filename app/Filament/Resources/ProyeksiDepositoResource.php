<?php

namespace App\Filament\Resources;

use App\Exports\RekeningPelengkap;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProyeksiDeposito;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\BelongsToColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProyeksiDepositoResource\Pages;
use Webbingbrasil\FilamentAdvancedFilter\Filters\TextFilter;
use Webbingbrasil\FilamentAdvancedFilter\Filters\BooleanFilter;
use App\Filament\Resources\ProyeksiDepositoResource\RelationManagers;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;



class ProyeksiDepositoResource extends Resource
{
    protected static ?string $model = ProyeksiDeposito::class;

    protected static ?string $navigationLabel = 'Data Proyeksi Deposito';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('rek_deposito')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_nasabah')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('jangka_waktu')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('nilai_bunga')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('saldo_valuta_awal')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('bunga')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total_bunga')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total_pajak')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total_bayar')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('tujuan_penggunaan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tanggal_bayar')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('jatuh_tempo'),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rek_deposito')
                    ->searchable()
                    ->copyable()
                    ->alignment(Alignment::Center),
                Tables\Columns\TextColumn::make('nama_nasabah')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nilai_bunga')
                    ->numeric()
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(function ($state) {
                        return $state . ' %';
                    }),
                Tables\Columns\TextColumn::make('saldo_valuta_awal')
                    ->numeric()
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(function ($state) {
                        return 'Rp ' . number_format($state, 0, ',', '.');
                    }),
                Tables\Columns\TextColumn::make('bunga')
                    ->numeric()
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_bunga')
                    ->numeric()
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_pajak')
                    ->numeric()
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('rekening.bank_tujuan')
                    ->searchable()
                    ->label('Bank Tujuan'),
                Tables\Columns\TextColumn::make('rekening.norek_tujuan')
                    ->searchable()
                    ->label('NoRek Tujuan'),
                Tables\Columns\TextColumn::make('rekening.nama_rekening')
                    ->searchable()
                    ->label('Nama Rekening'),
                Tables\Columns\TextColumn::make('rekening.kode_bank')
                    ->searchable()
                    ->label('Kode Bank'),
                Tables\Columns\TextColumn::make('total_bayar')
                    ->numeric()
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(function ($state) {
                        return 'Rp ' . number_format($state, 0, ',', '.');
                    }),

                Tables\Columns\TextColumn::make('tanggal_bayar')
                    ->searchable()
                    ->alignment(Alignment::Center),
                Tables\Columns\TextColumn::make('jatuh_tempo')
                    ->date()
                    ->sortable()
                    ->alignment(Alignment::Center),
                Tables\Columns\TextColumn::make('status')
                    ->numeric()
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(function ($state) {
                        return $state == 1 ? 'Aktif' : 'Tidak Aktif';
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->query(ProyeksiDeposito::query())
            ->filters([
                Tables\Filters\Filter::make('tanpa_rekening')
                    ->query(fn(Builder $query) => $query->whereDoesntHave('rekening')),
            ])
            //], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export')
                        ->label('Data Tidak Lengkap')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('primary')
                        ->action(function (Collection $records) {
                            //dd($records);
                            $firstRecord = $records->first();
                            if ($firstRecord && isset($firstRecord->tanggal_bayar)) {
                                $day = $firstRecord->tanggal_bayar;
                                $month = date('m'); 
                                $year = date('Y'); 
                                $dateString = "$year-$month-$day"; 
                                $date = \Carbon\Carbon::parse($dateString)->format('d-m-Y');
                            } else {
                                $date = date('d-m-Y');
                            }
                            $fileName = 'Data_Rekening_Pembayaran_' . $date . '(Tidak Lengkap).xlsx';
                            return Excel::download(new RekeningPelengkap($records), $fileName);
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProyeksiDepositos::route('/'),
            'create' => Pages\CreateProyeksiDeposito::route('/create'),
            'edit' => Pages\EditProyeksiDeposito::route('/{record}/edit'),
        ];
    }
}
