<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\RekeningTransfer;
use Filament\Resources\Resource;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\RekeningTransferResource\Pages;
use App\Filament\Resources\RekeningTransferResource\RelationManagers;

class RekeningTransferResource extends Resource
{
    protected static ?string $model = RekeningTransfer::class;
    protected static ?string $navigationLabel = 'Rekening Tujuan Pembayaran';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('norek_deposito')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_deposan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('norek_tujuan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('bank_tujuan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_rekening')
                    ->maxLength(255),
                Forms\Components\TextInput::make('nominal')
                    ->maxLength(255),
                Forms\Components\TextInput::make('tgl_bayar')
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('norek_deposito')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_deposan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('norek_tujuan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_tujuan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kode_bank')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_rekening')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nominal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tgl_bayar')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('bank_tujuan')
                    ->label('Bank Tujuan')
                    ->multiple()
                    ->searchable()
                    ->options(RekeningTransfer::distinct()->pluck('bank_tujuan', 'bank_tujuan')->filter(fn($value) => $value !== null)),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListRekeningTransfers::route('/'),
            'create' => Pages\CreateRekeningTransfer::route('/create'),
            'edit' => Pages\EditRekeningTransfer::route('/{record}/edit'),
        ];
    }
}
