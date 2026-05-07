<?php

namespace App\Filament\Resources\RedeemCodes\Schemas;

use App\Models\DigitalProduct;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RedeemCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Redeem')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g. DISKONHEMAT')
                    ->helperText('Otomatis dikonversi ke UPPERCASE')
                    ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                Select::make('type')
                    ->label('Tipe Output')
                    ->options([
                        'discount'    => '💰 Diskon (Potongan Harga)',
                        'custom_text' => '📝 Custom Text (Pesan/Bonus)',
                    ])
                    ->required()
                    ->native(false)
                    ->live()
                    ->default('discount'),
                TextInput::make('discount_value')
                    ->label('Nilai Diskon')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->placeholder('5000')
                    ->helperText('Potongan harga dalam Rupiah')
                    ->visible(fn ($get) => $get('type') === 'discount'),
                Textarea::make('custom_text')
                    ->label('Custom Text / Pesan')
                    ->placeholder('e.g. 🎉 Selamat! Anda mendapatkan bonus...')
                    ->maxLength(500)
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('type') === 'custom_text'),
                Select::make('applicable_products')
                    ->label('Produk yang Berlaku')
                    ->multiple()
                    ->options(function () {
                        return DigitalProduct::active()
                            ->orderBy('kode_produk')
                            ->get()
                            ->mapWithKeys(fn ($p) => [
                                $p->kode_produk => "{$p->kode_produk} — {$p->nama_produk}",
                            ]);
                    })
                    ->maxItems(5)
                    ->searchable()
                    ->helperText('Maks 5 produk. Kosongkan = berlaku untuk SEMUA produk.')
                    ->columnSpanFull(),
                TextInput::make('max_usage')
                    ->label('Maks Penggunaan')
                    ->numeric()
                    ->default(0)
                    ->helperText('0 = unlimited / tanpa batas'),
                TextInput::make('used_count')
                    ->label('Sudah Digunakan')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false),
                DateTimePicker::make('valid_from')
                    ->label('Berlaku Dari')
                    ->helperText('Kosongkan = langsung berlaku'),
                DateTimePicker::make('valid_until')
                    ->label('Berlaku Sampai')
                    ->helperText('Kosongkan = tidak ada batas waktu')
                    ->after('valid_from'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
