<?php

namespace App\Filament\Resources;

use App\Filament\Exports\RekeningTransferExporter;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Actions\ExportAction;
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
        $bankCodes = [
            'CENAIDJA' => 'BCA - CENAIDJA',
            'BRINIDJA' => 'BRI - BRINIDJA',
            'BMRIIDJA' => 'BANK MANDIRI - BMRIIDJA',
            'BNINIDJA' => 'BANK BNI - BNINIDJA',
            'BDINIDJA' => 'BANK DANAMON - BDINIDJA',
            'SYBDIDJ1' => 'BANK DANAMON UUS - SYBDIDJ1',
            'BBBAIDJA' => 'BANK PERMATA - BBBAIDJA',
            'SYBBIDJ1' => 'BANK PERMATA UUS - SYBBIDJ1',
            'IBBKIDJA' => 'BANK MAYBANK - IBBKIDJA',
            'SYBKIDJ1' => 'BANK MAYBANK UUS - SYBKIDJ1',
            'PINBIDJA' => 'PANIN BANK - PINBIDJA',
            'BNIAIDJA' => 'BANK CIMB - BNIAIDJA',
            'SYNAIDJ1' => 'BANK CIMB NIAGA UUS - SYNAIDJ1',
            'BBIJIDJA' => 'UOB INDONESIA - BBIJIDJA',
            'NISPIDJA' => 'BANK OCBC NISP - NISPIDJA',
            'SYONIDJ1' => 'BANK OCBC NISP - UUS - SYONIDJ1',
            'CITIIDJX' => 'CITIBANK - CITIIDJX',
            'CHASIDJX' => 'JPMORGAN BANK - CHASIDJX',
            'BOFAID2X' => 'BOA - BOFAID2X',
            'MCORIDJA' => 'BANK WINDU KENTJANA - MCORIDJA',
            'ARTGIDJA' => 'BAG INTERNASIONAL - ARTGIDJA',
            'BKKBIDJA' => 'BANGKOK BANK - BKKBIDJA',
            'HSBCIDJA' => 'HSBC - HSBCIDJA',
            'BOTKIDJX' => 'BTMU - BOTKIDJX',
            'SUNIIDJA' => 'SUMITOMO - SUNIIDJA',
            'DBSBIDJA' => 'DBS - DBSBIDJA',
            'BPIAIDJA' => 'BANK RESONA - BPIAIDJA',
            'MHCCIDJA' => 'BANK MIZUHO - MHCCIDJA',
            'SCBLIDJX' => 'STANDCHARD - SCBLIDJX',
            'ABNAIDJA' => 'RBS - ABNAIDJA',
            'BCIAIDJA' => 'BANK CAPITAL - BCIAIDJA',
            'BNPAIDJA' => 'BNP PARIBAS - BNPAIDJA',
            'ANZBIDJX' => 'ANZ INDONESIA - ANZBIDJX',
            'DEUTIDJA' => 'DEUTSCHE BANK - DEUTIDJA',
            'BKCHIDJA' => 'BOC - BKCHIDJA',
            'BBAIIDJA' => 'BBA - BBAIIDJA',
            'EKONIDJA' => 'BANK EKONOMI - EKONIDJA',
            'ANTDIDJD' => 'BANK ANTAR DAERAH - ANTDIDJD',
            'RABOIDJA' => 'RABOBANK - RABOIDJA',
            'CICTIDJ1' => 'BANK JTRUST - CICTIDJ1',
            'MAYAIDJA' => 'BANK MAYAPADA - MAYAIDJA',
            'PDJBIDJA' => 'BANK JABAR - PDJBIDJA',
            'BDKIIDJ1' => 'BANK DKI - BDKIIDJ1',
            'SYDKIDJ1' => 'BANK DKI UUS - SYDKIDJ1',
            'PDYKIDJ1' => 'BANK BPD DIY - PDYKIDJ1',
            'SYYKIDJ1' => 'BANK BPD DIY UUS - SYYKIDJ1',
            'PDJGIDJ1' => 'BANK JATENG - PDJGIDJ1',
            'SYJGIDJ1' => 'BANK JATENG UUS - SYJGIDJ1',
            'PDJTIDJ1' => 'BANK JATIM - PDJTIDJ1',
            'SYJTIDJ1' => 'BANK JATIM - UUS - SYJTIDJ1',
            'PDJMIDJ1' => 'BPD JAMBI - PDJMIDJ1',
            'PDACIDJ1' => 'BANK ACEH - PDACIDJ1',
            'SYACIDJ1' => 'BANK ACEH UUS - SYACIDJ1',
            'PDSUIDJ1' => 'BANK SUMUT - PDSUIDJ1',
            'SYSUIDJ1' => 'BPD SUMUT UUS - SYSUIDJ1',
            'PDSBIDJ1' => 'BANK NAGARI - PDSBIDJ1',
            'SYSBIDJ1' => 'BANK NAGARI - UUS - SYSBIDJ1',
            'PDRIIDJA' => 'BANK RIAU - PDRIIDJA',
            'BSSPIDSP' => 'BPD SUMSEL BABEL - BSSPIDSP',
            'SYSSIDJ1' => 'BPD SUMSEL BABEL UUS - SYSSIDJ1',
            'PDLPIDJ1' => 'BANK LAMPUNG - PDLPIDJ1',
            'PDKSIDJ1' => 'BPD KALSEL - PDKSIDJ1',
            'SYKSIDJ1' => 'BPD KALSEL UUS - SYKSIDJ1',
            'PDKBIDJ1' => 'BPD KALBAR - PDKBIDJ1',
            'SYKBIDJ1' => 'BPD KALBAR UUS - SYKBIDJ1',
            'PDKTIDJ1' => 'BPD KALTIM - PDKTIDJ1',
            'SYKTIDJ1' => 'BPD KALTIM UUS - SYKTIDJ1',
            'PDKGIDJ1' => 'BPD KALTENG - PDKGIDJ1',
            'PDWSIDJ1' => 'BANK SULSELBAR - PDWSIDJ1',
            'SYWSIDJ1' => 'BANK SULSELBAR UUS - SYWSIDJ1',
            'PDWUIDJ1' => 'BPD SULUT - PDWUIDJ1',
            'PDNBIDJ1' => 'BANK NTB - PDNBIDJ1',
            'ABALIDBS' => 'PT. BPD BALI - ABALIDBS',
            'PDNTIDJ1' => 'BPD NTT - PDNTIDJ1',
            'PDMLIDJ1' => 'BANK MALUKU - PDMLIDJ1',
            'PDIJIDJ1' => 'BPD PAPUA - PDIJIDJ1',
            'PDBKIDJ1' => 'BANK BENGKULU - PDBKIDJ1',
            'PDWGIDJ1' => 'BPD SULTENG - PDWGIDJ1',
            'PDWRIDJ1' => 'BPD SULTRA - PDWRIDJ1',
            'NUPAIDJ6' => 'BN PARAHYANGAN - NUPAIDJ6',
            'SWBAIDJ1' => 'BANK OF INDIA IND - SWBAIDJ1',
            'MUABIDJA' => 'BANK MUAMALAT - MUABIDJA',
            'MEDHIDS1' => 'BANK MESTIKA - MEDHIDS1',
            'MEEKIDJ1' => 'BANK SHINHAN IND - MEEKIDJ1',
            'SBJKIDJA' => 'BANK SINARMAS - SBJKIDJA',
            'SYTBIDJ1' => 'BANK SINARMAS UUS - SYTBIDJ1',
            'MASDIDJ1' => 'BANK MASPION - MASDIDJ1',
            'GNESIDJA' => 'BANK GANESHA - GNESIDJA',
            'ICBKIDJA' => 'BANK ICBC - ICBKIDJA',
            'AWANIDJA' => 'BANK QNB - AWANIDJA',
            'BTANIDJA' => 'BTN - BTANIDJA',
            'SYBTIDJ1' => 'BTN UUS - SYBTIDJ1',
            'BSDRIDJA' => 'BANK WOORI SAUDARA - BSDRIDJA',
            'SWAGIDJ1' => 'BANK VICTORIASYARIAH - SWAGIDJ1',
            'DJARIDJ1' => 'SYARIAH BRI - DJARIDJ1',
            'SYJBIDJ1' => 'BANK JABAR SYARIAH - SYJBIDJ1',
            'MEGAIDJA' => 'BANK MEGA - MEGAIDJA',
            'SYNIIDJ1' => 'BNI SYARIAH - SYNIIDJ1',
            'BBUKIDJA' => 'BUKOPIN - BBUKIDJA',
            'BSMDIDJA' => 'BSM - BSMDIDJA',
            'BUSTIDJ1' => 'BANK BISNIS - BUSTIDJ1',
            'RIPAIDJ1' => 'BANK ANDARA - RIPAIDJ1',
            'JSABIDJ1' => 'BANK JASA JAKARTA - JSABIDJ1',
            'HNBNIDJA' => 'BANK KEB HANA - HNBNIDJA',
            'BUMIIDJA' => 'MNC BANK - BUMIIDJA',
            'YUDBIDJ1' => 'BANK YUDHA BHAKTI - YUDBIDJ1',
            'MGABIDJ1' => 'BANK MITRANIAGA - MGABIDJ1',
            'AGTBIDJA' => 'AGRONIAGA - AGTBIDJA',
            'IDMOIDJ1' => 'BANK SBI - IDMOIDJ1',
            'ROYBIDJ1' => 'BANK ROYAL - ROYBIDJ1',
            'LFIBIDJ1' => 'BANK NATIONALNOBU - LFIBIDJ1',
            'BUTGIDJ1' => 'BANK MEGA SYARIAH - BUTGIDJ1',
            'INPBIDJ1' => 'BANK INA - INPBIDJ1',
            'ARFAIDJ1' => 'BANK PANIN SYARIAH - ARFAIDJ1',
            'PMASIDJ1' => 'PRIMA MASTER - PMASIDJ1',
            'SDOBIDJ1' => 'BANK SYARIAH BUKOPIN - SDOBIDJ1',
            'BDIPIDJ1' => 'BANK SAMPOERNA - BDIPIDJ1',
            'LMANIDJ1' => 'BANK DINAR - LMANIDJ1',
            'LOMAIDJ1' => 'BANK AMAR - LOMAIDJ1',
            'KSEBIDJ1' => 'BANK KESEJAHTERAAN - KSEBIDJ1',
            'SYCAIDJ1' => 'BANK BCA SYARIAH - SYCAIDJ1',
            'ATOSIDJ1' => 'BANK ARTOS - ATOSIDJ1',
            'PUBAIDJ1' => 'BANK BTPN SYARIAH - PUBAIDJ1',
            'MASBIDJ1' => 'Bank MAS - MASBIDJ1',
            'MAYOIDJA' => 'BANK MAYORA - MAYOIDJA',
            'BIDXIDJA' => 'BANK INDEX - BIDXIDJA',
            'EKSTIDJ1' => 'BANK PUNDI - EKSTIDJ1',
            'CNBAIDJ1' => 'BANK CNB - CNBAIDJ1',
            'FAMAIDJ1' => 'BANK FAMA - FAMAIDJ1',
            'SIHBIDJ1' => 'BANK MANDIRI TASPEN POS - SIHBIDJ1',
            'VICTIDJ1' => 'BANK VICTORIA - VICTIDJ1',
            'HRDAIDJ1' => 'BANK HARDA - HRDAIDJ1',
            'AGSSIDJA' => 'BANK AGRIS - AGSSIDJA',
            'MBBEIDJA' => 'MAYBANK SYARIAH - MBBEIDJA',
            'CTCBIDJA' => 'CTBC INDONESIA - CTCBIDJA',
            'BICNIDJA' => 'BANK COMMONWEALTH - BICNIDJA',
        ];

        $reversedBankCodes = array_flip($bankCodes);

        return $form
            ->schema([
                Forms\Components\TextInput::make('norek_deposito')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_deposan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('norek_tujuan')
                    ->maxLength(255),
                Forms\Components\Select::make('bank_tujuan')
                    ->label('Bank Tujuan')
                    ->options($bankCodes)
                    ->placeholder('Pilih Bank')
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) use ($bankCodes) {
                        $set('kode_bank', $bankCodes[$state] ?? null);
                    }),

                Forms\Components\TextInput::make('kode_bank')
                    ->required()
                    ->readOnly(true),
                Forms\Components\TextInput::make('nama_rekening')
                    ->maxLength(255),
                Forms\Components\TextInput::make('nominal')
                    ->maxLength(255),
                Forms\Components\TextInput::make('tgl_bayar')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->headerActions([
            //     ExportAction::make()
            //         ->exporter(RekeningTransferExporter::class)
            //  ])
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
            'view' => Pages\ViewRekeningTransfer::route('/{record}'),
            'edit' => Pages\EditRekeningTransfer::route('/{record}/edit'),
        ];
    }
}
