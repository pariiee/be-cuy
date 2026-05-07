<?php

namespace App\Filament\Resources\ProductMarkups;

use App\Filament\Resources\ProductMarkups\Pages\CreateProductMarkup;
use App\Filament\Resources\ProductMarkups\Pages\EditProductMarkup;
use App\Filament\Resources\ProductMarkups\Pages\ListProductMarkups;
use App\Filament\Resources\ProductMarkups\Schemas\ProductMarkupForm;
use App\Filament\Resources\ProductMarkups\Tables\ProductMarkupsTable;
use App\Models\ProductMarkup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProductMarkupResource extends Resource
{
    protected static ?string $model = ProductMarkup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?string $recordTitleAttribute = 'product_code';

    public static function getNavigationLabel(): string
    {
        return 'Markup Harga';
    }

    public static function form(Schema $schema): Schema
    {
        return ProductMarkupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductMarkupsTable::configure($table);
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
            'index' => ListProductMarkups::route('/'),
            'create' => CreateProductMarkup::route('/create'),
            'edit' => EditProductMarkup::route('/{record}/edit'),
        ];
    }
}
