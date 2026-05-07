<?php

namespace App\Filament\Resources\RedeemCodes;

use App\Filament\Resources\RedeemCodes\Pages\CreateRedeemCode;
use App\Filament\Resources\RedeemCodes\Pages\EditRedeemCode;
use App\Filament\Resources\RedeemCodes\Pages\ListRedeemCodes;
use App\Filament\Resources\RedeemCodes\Schemas\RedeemCodeForm;
use App\Filament\Resources\RedeemCodes\Tables\RedeemCodesTable;
use App\Models\RedeemCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RedeemCodeResource extends Resource
{
    protected static ?string $model = RedeemCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static UnitEnum|string|null $navigationGroup = 'Produk Digital';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationLabel(): string
    {
        return 'Kode Redeem';
    }

    public static function getModelLabel(): string
    {
        return 'Kode Redeem';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Kode Redeem';
    }

    public static function form(Schema $schema): Schema
    {
        return RedeemCodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RedeemCodesTable::configure($table);
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
            'index' => ListRedeemCodes::route('/'),
            'create' => CreateRedeemCode::route('/create'),
            'edit' => EditRedeemCode::route('/{record}/edit'),
        ];
    }
}
