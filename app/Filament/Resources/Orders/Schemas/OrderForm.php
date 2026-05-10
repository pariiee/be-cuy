<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('User')
                    ->searchable()
                    ->required(),
                Select::make('provider')
                    ->options([
                        'okeconnect' => 'OkeConnect',
                        'smmpanel' => 'SMM Panel',
                    ])
                    ->required(),
                TextInput::make('order_ref')
                    ->label('Ref ID'),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])
                    ->default('pending')
                    ->required(),
                TextInput::make('product_code')
                    ->label('Kode Produk'),
                TextInput::make('product_name')
                    ->label('Nama Produk'),
                TextInput::make('category')
                    ->label('Kategori'),
                TextInput::make('target')
                    ->label('Target')
                    ->required(),
                TextInput::make('quantity')
                    ->label('Jumlah')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('base_price')
                    ->label('Harga Modal')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('markup')
                    ->label('Markup')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('sell_price')
                    ->label('Harga Jual')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('profit')
                    ->label('Profit')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                Select::make('payment_method')
                    ->label('Metode Bayar')
                    ->options([
                        'balance' => 'Saldo',
                        'midtrans' => 'Midtrans',
                    ])
                    ->default('balance')
                    ->required(),
                TextInput::make('payment_fee')
                    ->label('Fee Pembayaran')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('total_pay')
                    ->label('Total Bayar')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
