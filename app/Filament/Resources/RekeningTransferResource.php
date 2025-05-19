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
                        'BCA' => 'CENAIDJA',
                        'BRI' => 'BRINIDJA',
                        'BANK MANDIRI' => 'BMRIIDJA',
                        'BANK BNI' => 'BNINIDJA',
                        'BANK DANAMON' => 'BDINIDJA',
                        'BANK DANAMON UUS' => 'SYBDIDJ1',
                        'BANK PERMATA' => 'BBBAIDJA',
                        'BANK PERMATA UUS' => 'SYBBIDJ1',
                        'BANK MAYBANK' => 'IBBKIDJA',
                        'BANK MAYBANK UUS' => 'SYBKIDJ1',
                        'PANIN BANK' => 'PINBIDJA',
                        'BANK CIMB' => 'BNIAIDJA',
                        'BANK CIMB NIAGA UUS' => 'SYNAIDJ1',
                        'UOB INDONESIA' => 'BBIJIDJA',
                        'BANK OCBC NISP' => 'NISPIDJA',
                        'BANK OCBC NISP - UUS' => 'SYONIDJ1',
                        'CITIBANK' => 'CITIIDJX',
                        'JPMORGAN BANK' => 'CHASIDJX',
                        'BOA' => 'BOFAID2X',
                        'BANK WINDU KENTJANA' => 'MCORIDJA',
                        'BAG INTERNASIONAL' => 'ARTGIDJA',
                        'BANGKOK BANK' => 'BKKBIDJA',
                        'HSBC' => 'HSBCIDJA',
                        'BTMU' => 'BOTKIDJX',
                        'SUMITOMO' => 'SUNIIDJA',
                        'DBS' => 'DBSBIDJA',
                        'BANK RESONA' => 'BPIAIDJA',
                        'BANK MIZUHO' => 'MHCCIDJA',
                        'STANDCHARD' => 'SCBLIDJX',
                        'RBS' => 'ABNAIDJA',
                        'BANK CAPITAL' => 'BCIAIDJA',
                        'BNP PARIBAS' => 'BNPAIDJA',
                        'ANZ INDONESIA' => 'ANZBIDJX',
                        'DEUTSCHE BANK' => 'DEUTIDJA',
                        'BOC' => 'BKCHIDJA',
                        'BBA' => 'BBAIIDJA',
                        'BANK EKONOMI' => 'EKONIDJA',
                        'BANK ANTAR DAERAH' => 'ANTDIDJD',
                        'RABOBANK' => 'RABOIDJA',
                        'BANK JTRUST' => 'CICTIDJ1',
                        'BANK MAYAPADA' => 'MAYAIDJA',
                        'BANK JABAR' => 'PDJBIDJA',
                        'BANK DKI' => 'BDKIIDJ1',
                        'BANK DKI UUS' => 'SYDKIDJ1',
                        'BANK BPD DIY' => 'PDYKIDJ1',
                        'BANK BPD DIY UUS' => 'SYYKIDJ1',
                        'BANK JATENG' => 'PDJGIDJ1',
                        'BANK JATENG UUS' => 'SYJGIDJ1',
                        'BANK JATIM' => 'PDJTIDJ1',
                        'BANK JATIM - UUS' => 'SYJTIDJ1',
                        'BPD JAMBI' => 'PDJMIDJ1',
                        'BANK ACEH' => 'PDACIDJ1',
                        'BANK ACEH UUS' => 'SYACIDJ1',
                        'BANK SUMUT' => 'PDSUIDJ1',
                        'BPD SUMUT UUS' => 'SYSUIDJ1',
                        'BANK NAGARI' => 'PDSBIDJ1',
                        'BANK NAGARI - UUS' => 'SYSBIDJ1',
                        'BANK RIAU' => 'PDRIIDJA',
                        'BPD SUMSEL BABEL' => 'BSSPIDSP',
                        'BPD SUMSEL BABEL UUS' => 'SYSSIDJ1',
                        'BANK LAMPUNG' => 'PDLPIDJ1',
                        'BPD KALSEL' => 'PDKSIDJ1',
                        'BPD KALSEL UUS' => 'SYKSIDJ1',
                        'BPD KALBAR' => 'PDKBIDJ1',
                        'BPD KALBAR UUS' => 'SYKBIDJ1',
                        'BPD KALTIM' => 'PDKTIDJ1',
                        'BPD KALTIM UUS' => 'SYKTIDJ1',
                        'BPD KALTENG' => 'PDKGIDJ1',
                        'BANK SULSELBAR' => 'PDWSIDJ1',
                        'BANK SULSELBAR UUS' => 'SYWSIDJ1',
                        'BPD SULUT' => 'PDWUIDJ1',
                        'BANK NTB' => 'PDNBIDJ1',
                        'PT. BPD BALI' => 'ABALIDBS',
                        'BPD NTT' => 'PDNTIDJ1',
                        'BANK MALUKU' => 'PDMLIDJ1',
                        'BPD PAPUA' => 'PDIJIDJ1',
                        'BANK BENGKULU' => 'PDBKIDJ1',
                        'BPD SULTENG' => 'PDWGIDJ1',
                        'BPD SULTRA' => 'PDWRIDJ1',
                        'BN PARAHYANGAN' => 'NUPAIDJ6',
                        'BANK OF INDIA IND' => 'SWBAIDJ1',
                        'BANK MUAMALAT' => 'MUABIDJA',
                        'BANK MESTIKA' => 'MEDHIDS1',
                        'BANK SHINHAN IND' => 'MEEKIDJ1',
                        'BANK SINARMAS' => 'SBJKIDJA',
                        'BANK SINARMAS UUS' => 'SYTBIDJ1',
                        'BANK MASPION' => 'MASDIDJ1',
                        'BANK GANESHA' => 'GNESIDJA',
                        'BANK ICBC' => 'ICBKIDJA',
                        'BANK QNB' => 'AWANIDJA',
                        'BTN' => 'BTANIDJA',
                        'BTN UUS' => 'SYBTIDJ1',
                        'BANK WOORI SAUDARA' => 'BSDRIDJA',
                        'BTPN' => 'SUNIIDJA',
                        'BANK VICTORIASYARIAH' => 'SWAGIDJ1',
                        'SYARIAH BRI' => 'DJARIDJ1',
                        'BANK JABAR SYARIAH' => 'SYJBIDJ1',
                        'BANK MEGA' => 'MEGAIDJA',
                        'BNI SYARIAH' => 'SYNIIDJ1',
                        'BUKOPIN' => 'BBUKIDJA',
                        'BSM' => 'BSMDIDJA',
                        'BANK BISNIS' => 'BUSTIDJ1',
                        'BANK ANDARA' => 'RIPAIDJ1',
                        'BANK JASA JAKARTA' => 'JSABIDJ1',
                        'BANK KEB HANA' => 'HNBNIDJA',
                        'MNC BANK' => 'BUMIIDJA',
                        'BANK YUDHA BHAKTI' => 'YUDBIDJ1',
                        'BANK MITRANIAGA' => 'MGABIDJ1',
                        'AGRONIAGA' => 'AGTBIDJA',
                        'BANK SBI' => 'IDMOIDJ1',
                        'BANK ROYAL' => 'ROYBIDJ1',
                        'BANK NATIONALNOBU' => 'LFIBIDJ1',
                        'BANK MEGA SYARIAH' => 'BUTGIDJ1',
                        'BANK INA' => 'INPBIDJ1',
                        'BANK PANIN SYARIAH' => 'ARFAIDJ1',
                        'PRIMA MASTER' => 'PMASIDJ1',
                        'BANK SYARIAH BUKOPIN' => 'SDOBIDJ1',
                        'BANK SAMPOERNA' => 'BDIPIDJ1',
                        'BANK DINAR' => 'LMANIDJ1',
                        'BANK AMAR' => 'LOMAIDJ1',
                        'BANK KESEJAHTERAAN' => 'KSEBIDJ1',
                        'BANK BCA SYARIAH' => 'SYCAIDJ1',
                        'BANK ARTOS' => 'ATOSIDJ1',
                        'BANK BTPN SYARIAH' => 'PUBAIDJ1',
                        'Bank MAS' => 'MASBIDJ1',
                        'BANK MAYORA' => 'MAYOIDJA',
                        'BANK INDEX' => 'BIDXIDJA',
                        'BANK PUNDI' => 'EKSTIDJ1',
                        'BANK CNB' => 'CNBAIDJ1',
                        'BANK FAMA' => 'FAMAIDJ1',
                        'BANK MANDIRI TASPEN POS' => 'SIHBIDJ1',
                        'BANK VICTORIA' => 'VICTIDJ1',
                        'BANK HARDA' => 'HRDAIDJ1',
                        'BANK AGRIS' => 'AGSSIDJA',
                        'MAYBANK SYARIAH' => 'MBBEIDJA',
                        'CTBC INDONESIA' => 'CTCBIDJA',
                        'BANK COMMONWEALTH' => 'BICNIDJA',
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
                Forms\Components\TextInput::make('bank_tujuan')
                    ->maxLength(255),
                Forms\Components\Select::make('kode_bank')
                    ->options($reversedBankCodes)
                    ->placeholder('Pilih Bank')
                    ->searchable()
                    ->required(),
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
