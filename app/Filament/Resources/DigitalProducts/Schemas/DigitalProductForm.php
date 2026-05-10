<?php

namespace App\Filament\Resources\DigitalProducts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DigitalProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_produk')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(200)
                    ->placeholder('e.g. CAPCUT 35H'),
                TextInput::make('kode_produk')
                    ->label('Kode Produk')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g. CC35H'),
                TextInput::make('harga_user')
                    ->label('Harga User')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('50000'),
                TextInput::make('harga_reseller')
                    ->label('Harga Reseller')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('45000'),
                TextInput::make('garansi')
                    ->label('Garansi (hari)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->placeholder('0')
                    ->helperText('0 = tidak ada garansi'),
                Textarea::make('stok_items')
                    ->label('Isi Stok')
                    ->rows(8)
                    ->placeholder("gmail@contoh.com | password123\ngmail@contoh2.com | password456\n...")
                    ->helperText('Satu item per baris. Setiap baris = 1 stok. Produk otomatis aktif setelah ada stok.')
                    ->columnSpanFull()
                    ->dehydrated(false),
                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->maxLength(1000)
                    ->placeholder('Deskripsi produk (opsional)')
                    ->columnSpanFull(),
            ]);
    }
}
