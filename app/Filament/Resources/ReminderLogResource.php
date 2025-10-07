<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ReminderLogResource\Pages;
use App\Models\ReminderLog;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class ReminderLogResource extends Resource
{
    protected static ?string $model = ReminderLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
     protected static ?string $navigationGroup = 'Setting Reminder';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Reminder Logs';
    protected static ?string $pluralModelLabel = 'Reminder Logs';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('kode_cabang')
                    ->label('Kode Cabang')
                    ->sortable()
                    ->searchable(),

                // Tables\Columns\TextColumn::make('cabang_nama')
                //     ->label('Nama Cabang')
                //     ->sortable()
                //     ->searchable(),

                Tables\Columns\BadgeColumn::make('channel')
                    ->label('Channel')
                    ->colors([
                        'success' => 'email',
                        'warning' => 'wa',
                    ])
                    ->formatStateUsing(fn($state) => strtoupper($state)),

                Tables\Columns\TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'success',
                        'danger'  => 'failed',
                        'warning' => 'pending',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('count')
                    ->label('Jumlah')
                    ->sortable(),

                Tables\Columns\TextColumn::make('message')
                    ->label('Pesan')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('response')
                    ->label('Response')
                    ->limit(60)
                    ->tooltip(fn($record) => $record->response),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('channel')
                    ->options([
                        'email' => 'Email',
                        'wa' => 'WhatsApp',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'pending' => 'Pending',
                    ]),
                // Tables\Filters\SelectFilter::make('kode_cabang')
                //     ->label('Cabang')
                //     ->options(ReminderLog::select('kode_cabang', 'cabang_nama')
                //         ->distinct()
                //         ->pluck('cabang_nama', 'kode_cabang')),
                Tables\Filters\Filter::make('created_at')
                    ->label('Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReminderLogs::route('/'),
        ];
    }
}
