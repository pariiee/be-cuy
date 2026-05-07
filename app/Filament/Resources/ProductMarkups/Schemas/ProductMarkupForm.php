<?php

namespace App\Filament\Resources\ProductMarkups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductMarkupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('provider')
                    ->options([
                        'okeconnect' => 'OkeConnect (Pulsa/Game/PLN)',
                        'smmpanel' => 'SMM Panel (Sosmed)',
                    ])
                    ->required()
                    ->default('okeconnect'),
                Select::make('markup_type')
                    ->label('Tipe Markup')
                    ->options([
                        'fixed' => 'Nominal Tetap (Rp)',
                        'percentage' => 'Persentase (%)',
                    ])
                    ->default('fixed')
                    ->required(),
                TextInput::make('markup_value')
                    ->label('Nilai Markup')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('Contoh: 500 (Rp500) atau 5 (5%)'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
