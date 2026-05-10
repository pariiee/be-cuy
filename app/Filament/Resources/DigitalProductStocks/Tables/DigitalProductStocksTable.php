<?php

namespace App\Filament\Resources\DigitalProductStocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DigitalProductStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.nama_produk')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40),
                TextColumn::make('product.kode_produk')
                    ->label('Kode Produk')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('content')
                    ->label('Isi Stok')
                    ->searchable()
                    ->limit(50)
                    ->copyable()
                    ->tooltip(fn ($state) => $state),
                TextColumn::make('is_sold')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Terjual' : 'Tersedia')
                    ->color(fn ($state) => $state ? 'danger' : 'success'),
                TextColumn::make('buyer.name')
                    ->label('Pembeli')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('order_ref')
                    ->label('Ref Order')
                    ->placeholder('—')
                    ->copyable()
                    ->limit(20),
                TextColumn::make('sold_at')
                    ->label('Terjual Pada')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'nama_produk')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_sold')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Terjual')
                    ->falseLabel('Tersedia'),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
