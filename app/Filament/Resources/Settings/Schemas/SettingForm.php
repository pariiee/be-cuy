<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Info Setting')
                    ->schema([
                        TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Kunci unik setting (tidak bisa diubah)'),
                        TextInput::make('label')
                            ->label('Label / Keterangan')
                            ->required(),
                    ])->columns(2),

                Section::make('Nilai')
                    ->schema([
                        TextInput::make('value')
                            ->label('Nilai')
                            ->required()
                            ->helperText('Contoh: 500 (untuk fixed Rp500) atau 2.5 (untuk 2.5%)'),
                        Select::make('type')
                            ->label('Tipe Data')
                            ->options([
                                'string' => 'String (Teks)',
                                'integer' => 'Integer (Angka Bulat)',
                                'decimal' => 'Decimal (Angka Desimal)',
                                'boolean' => 'Boolean (Ya/Tidak)',
                                'json' => 'JSON',
                            ])
                            ->required()
                            ->default('string'),
                        Select::make('group')
                            ->label('Grup')
                            ->options([
                                'general' => 'Umum',
                                'payment' => 'Pembayaran',
                            ])
                            ->required()
                            ->default('general'),
                    ])->columns(3),
            ]);
    }
}
