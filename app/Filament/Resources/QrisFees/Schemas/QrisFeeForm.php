<?php

namespace App\Filament\Resources\QrisFees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QrisFeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('purpose')
                    ->label('Tujuan')
                    ->options([
                        'deposit' => 'Deposit (Top Up Saldo)',
                        'transaction' => 'Transaksi (Pembelian Produk)',
                    ])
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Setiap tujuan hanya boleh punya 1 markup aktif'),
                Select::make('fee_type')
                    ->label('Tipe Markup')
                    ->options([
                        'fixed' => 'Nominal Tetap (Rp)',
                        'percentage' => 'Persentase (%)',
                    ])
                    ->default('percentage')
                    ->required(),
                TextInput::make('fee_value')
                    ->label('Nilai Markup')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('Contoh: 500 (Rp500) atau 0.7 (0.7%). Admin & Reseller tidak kena markup.'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->placeholder('Catatan tambahan (opsional)')
                    ->columnSpanFull(),
            ]);
    }
}
