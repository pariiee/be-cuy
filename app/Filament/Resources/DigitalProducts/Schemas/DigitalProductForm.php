<?php

namespace App\Filament\Resources\DigitalProducts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                TextInput::make('app_category')
                    ->label('Kategori Apps')
                    ->maxLength(100)
                    ->placeholder('e.g. Video Editing, Social Media')
                    ->helperText('Klasifikasi jenis aplikasi produk'),
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
                TextInput::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->placeholder('10')
                    ->helperText('Jumlah stok tersedia. 0 = habis, tidak bisa dipesan.'),
                Toggle::make('garansi')
                    ->label('Garansi')
                    ->default(false),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->maxLength(1000)
                    ->placeholder('Deskripsi produk (opsional)')
                    ->columnSpanFull(),
            ]);
    }
}
