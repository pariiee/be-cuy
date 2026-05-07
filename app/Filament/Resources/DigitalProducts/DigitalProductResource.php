<?php

namespace App\Filament\Resources\DigitalProducts;

use App\Filament\Resources\DigitalProducts\Pages\CreateDigitalProduct;
use App\Filament\Resources\DigitalProducts\Pages\EditDigitalProduct;
use App\Filament\Resources\DigitalProducts\Pages\ListDigitalProducts;
use App\Filament\Resources\DigitalProducts\Schemas\DigitalProductForm;
use App\Filament\Resources\DigitalProducts\Tables\DigitalProductsTable;
use App\Models\DigitalProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DigitalProductResource extends Resource
{
    protected static ?string $model = DigitalProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static UnitEnum|string|null $navigationGroup = 'Produk Digital';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nama_produk';

    public static function getNavigationLabel(): string
    {
        return 'Produk';
    }

    public static function getModelLabel(): string
    {
        return 'Produk';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Produk';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count() ?: null;
    }

    public static function form(Schema $schema): Schema
    {
        return DigitalProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DigitalProductsTable::configure($table);
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
            'index' => ListDigitalProducts::route('/'),
            'create' => CreateDigitalProduct::route('/create'),
            'edit' => EditDigitalProduct::route('/{record}/edit'),
        ];
    }
}
