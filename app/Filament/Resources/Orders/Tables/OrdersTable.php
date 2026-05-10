<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
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
                TextColumn::make('provider')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'okeconnect' => 'success',
                        'smmpanel' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('product_name')
                    ->label('Produk')
                    ->limit(30)
                    ->searchable(),
                TextColumn::make('target')
                    ->label('Target')
                    ->limit(20)
                    ->searchable(),
                TextColumn::make('sell_price')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('profit')
                    ->label('Profit')
                    ->money('IDR')
                    ->sortable()
                    ->color('success'),
                TextColumn::make('payment_status')
                    ->label('Bayar')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'lunas' => 'Lunas',
                        'belum' => 'Belum Lunas',
                        default => 'Belum Lunas',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'lunas' => 'success',
                        'belum' => 'danger',
                        default => 'danger',
                    }),
                TextColumn::make('payment_method')
                    ->label('Via')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'balance' => 'Saldo',
                        'midtrans' => 'Midtrans',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'balance' => 'gray',
                        'midtrans' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('total_pay')
                    ->label('Total Bayar')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'processing' => 'warning',
                        'pending' => 'gray',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('provider')
                    ->options([
                        'okeconnect' => 'OkeConnect',
                        'smmpanel' => 'SMM Panel',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
