<?php

namespace App\Filament\Resources\Deposits\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DepositForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Info Pembayaran')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('User')
                            ->searchable()
                            ->required(),
                        TextInput::make('invoice_number')
                            ->label('No Invoice')
                            ->required(),
                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Select::make('purpose')
                            ->label('Tujuan')
                            ->options([
                                'deposit' => 'Top Up Saldo',
                                'order_payment' => 'Bayar Order',
                            ])
                            ->required()
                            ->default('deposit'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu',
                                'paid' => 'Dibayar',
                                'expired' => 'Expired',
                                'failed' => 'Gagal',
                            ])
                            ->default('pending')
                            ->required(),
                        Select::make('order_id')
                            ->relationship('order', 'order_ref')
                            ->label('Order Terkait')
                            ->searchable()
                            ->placeholder('Tidak ada'),
                    ])->columns(3),

                Section::make('Info Midtrans')
                    ->schema([
                        TextInput::make('midtrans_snap_token')
                            ->label('Snap Token'),
                        TextInput::make('midtrans_transaction_id')
                            ->label('Transaction ID'),
                        TextInput::make('midtrans_payment_type')
                            ->label('Tipe Pembayaran'),
                        TextInput::make('midtrans_va_number')
                            ->label('VA Number'),
                    ])->columns(2),

                Section::make('Info Pembayar')
                    ->schema([
                        TextInput::make('payment_customer_name')
                            ->label('Nama Pembayar'),
                        TextInput::make('payment_method_by')
                            ->label('Dibayar Via'),
                        DateTimePicker::make('paid_at')
                            ->label('Waktu Bayar'),
                    ])->columns(3),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
