<?php

namespace App\Filament\Resources;

use App\Models\DepositoReminder;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;

class DepositoReminderResource extends Resource
{
    protected static ?string $model = DepositoReminder::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup = 'Setting Reminder';
    protected static ?string $navigationLabel = 'Reminder Deposito';
    protected static ?int $navigationSort = 5;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kode_cabang')
                    ->label('Cabang')
                    ->options([
                        '00' => 'KPM',
                        '01' => 'KPO',
                        '02' => 'BOGOR',
                        '03' => 'DEPOK',
                        '04' => 'TANGERANG',
                        '05' => 'JAKARTA TIMUR',
                        '06' => 'KARAWANG',
                        '07' => 'CIKARANG',
                        '08' => 'PURWOKERTO',
                    ])
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('email_tujuan')
                    ->label('Email Tujuan')
                    ->email()
                    ->maxLength(150),

                Forms\Components\TextInput::make('wa_tujuan')
                    ->label('Nomor WA Tujuan')
                    ->maxLength(100)
                    ->placeholder('6281234567890'),

                Forms\Components\TextInput::make('hari_sebelum_jt')
                    ->label('Hari Sebelum Jatuh Tempo')
                    ->numeric()
                    ->default(7)
                    ->required(),

                Forms\Components\Toggle::make('aktif')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_cabang')
                    ->label('Cabang')
                    ->formatStateUsing(function (string $state): string {
                        $cabang = [
                            '00' => 'KPM',
                            '01' => 'KPO',
                            '02' => 'BOGOR',
                            '03' => 'DEPOK',
                            '04' => 'TANGERANG',
                            '05' => 'JAKARTA TIMUR',
                            '06' => 'KARAWANG',
                            '07' => 'CIKARANG',
                            '08' => 'PURWOKERTO',
                        ];

                        return $cabang[$state] ?? $state;
                    }),
                Tables\Columns\TextColumn::make('email_tujuan')->label('Email'),
                Tables\Columns\TextColumn::make('wa_tujuan')->label('WhatsApp'),
                Tables\Columns\TextColumn::make('hari_sebelum_jt')->label('H-'),
                Tables\Columns\IconColumn::make('aktif')->boolean()->label('Aktif'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->label('Update Terakhir'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => DepositoReminderResource\Pages\ListDepositoReminders::route('/'),
            'create' => DepositoReminderResource\Pages\CreateDepositoReminder::route('/create'),
            'edit' => DepositoReminderResource\Pages\EditDepositoReminder::route('/{record}/edit'),
        ];
    }
}
