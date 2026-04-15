<?php

namespace App\Filament\Resources\KategoriPengeluarans;

use App\Filament\Resources\KategoriPengeluarans\Pages\CreateKategoriPengeluaran;
use App\Filament\Resources\KategoriPengeluarans\Pages\EditKategoriPengeluaran;
use App\Filament\Resources\KategoriPengeluarans\Pages\ListKategoriPengeluarans;
use App\Models\KategoriPengeluaran;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use Filament\Facades\Filament;

class KategoriPengeluaranResource extends Resource
{
    protected static ?string $model = KategoriPengeluaran::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;
    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Kategori Pengeluaran';
    protected static ?string $pluralModelLabel = 'Kategori Pengeluaran';
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nama';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Kategori Pengeluaran')
                ->schema([
                    Forms\Components\TextInput::make('nama')
                        ->label('Nama kategori')
                        ->required()
                        ->maxLength(100),

                    /*Forms\Components\TextInput::make('deskripsi')
                        ->label('Deskripsi')
                        ->maxLength(255),*/

                    Forms\Components\Select::make('daftar_akun_id')
                        ->label('Akun Beban')
                        ->relationship(
                            name: 'akun',
                            titleAttribute: 'nama_akun',
                            modifyQueryUsing: fn (Builder $query) => $query->where('header_akun', 5),
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                /*TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50),*/

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListKategoriPengeluarans::route('/'),
            'create' => CreateKategoriPengeluaran::route('/create'),
            'edit'   => EditKategoriPengeluaran::route('/{record}/edit'),
        ];
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'finance';
    }

}
