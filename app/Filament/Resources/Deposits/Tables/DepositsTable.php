<?php

namespace App\Filament\Resources\Deposits\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DepositsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->limit(20),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('purpose')
                    ->label('Tujuan')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'deposit' => 'Top Up Saldo',
                        'order_payment' => 'Bayar Order',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'deposit' => 'info',
                        'order_payment' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'expired' => 'gray',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'paid' => 'Dibayar',
                        'pending' => 'Menunggu',
                        'expired' => 'Expired',
                        'failed' => 'Gagal',
                        default => $state,
                    }),
                TextColumn::make('payment_method_by')
                    ->label('Via')
                    ->placeholder('-'),
                TextColumn::make('payment_customer_name')
                    ->label('Pembayar')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('paid_at')
                    ->label('Waktu Bayar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('order_id')
                    ->label('Order #')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'paid' => 'Dibayar',
                        'expired' => 'Expired',
                        'failed' => 'Gagal',
                    ]),
                SelectFilter::make('purpose')
                    ->label('Tujuan')
                    ->options([
                        'deposit' => 'Top Up Saldo',
                        'order_payment' => 'Bayar Order',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->label('Ubah'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
