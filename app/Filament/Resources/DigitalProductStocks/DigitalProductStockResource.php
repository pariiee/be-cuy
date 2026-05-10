<?php

namespace App\Filament\Resources\DigitalProductStocks;

use App\Filament\Resources\DigitalProductStocks\Pages\ListDigitalProductStocks;
use App\Filament\Resources\DigitalProductStocks\Tables\DigitalProductStocksTable;
use App\Models\DigitalProductStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DigitalProductStockResource extends Resource
{
    protected static ?string $model = DigitalProductStock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static UnitEnum|string|null $navigationGroup = 'Produk Digital';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'content';

    public static function getNavigationLabel(): string
    {
        return 'Kelola Stok';
    }

    public static function getModelLabel(): string
    {
        return 'Stok';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Stok';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_sold', false)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('is_sold', false)->count();
        return $count > 10 ? 'success' : 'danger';
    }

    public static function table(Table $table): Table
    {
        return DigitalProductStocksTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDigitalProductStocks::route('/'),
        ];
    }
}
