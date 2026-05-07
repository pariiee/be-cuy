<?php

namespace App\Filament\Resources\QrisFees;

use App\Filament\Resources\QrisFees\Pages\CreateQrisFee;
use App\Filament\Resources\QrisFees\Pages\EditQrisFee;
use App\Filament\Resources\QrisFees\Pages\ListQrisFees;
use App\Filament\Resources\QrisFees\Schemas\QrisFeeForm;
use App\Filament\Resources\QrisFees\Tables\QrisFeesTable;
use App\Models\QrisFee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class QrisFeeResource extends Resource
{
    protected static ?string $model = QrisFee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return 'Markup QRIS';
    }

    public static function getModelLabel(): string
    {
        return 'Markup QRIS';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Markup QRIS';
    }

    public static function form(Schema $schema): Schema
    {
        return QrisFeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QrisFeesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQrisFees::route('/'),
            'create' => CreateQrisFee::route('/create'),
            'edit' => EditQrisFee::route('/{record}/edit'),
        ];
    }
}
