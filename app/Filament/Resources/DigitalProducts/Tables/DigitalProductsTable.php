<?php

namespace App\Filament\Resources\DigitalProducts\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DigitalProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_produk')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->color('primary'),
                TextColumn::make('nama_produk')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('harga_user')
                    ->label('Harga User')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('harga_reseller')
                    ->label('Harga Reseller')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->color('success'),
                TextColumn::make('garansi')
                    ->label('Garansi')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} hari" : '—')
                    ->badge()
                    ->color('info'),
                TextColumn::make('stok')
                    ->label('Stok')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state : 'Habis'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'nama_kategori'),
            ])
            ->recordActions([
                Action::make('restock')
                    ->label('Tambah Stok')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Textarea::make('items')
                            ->label('Item Stok Baru')
                            ->rows(8)
                            ->required()
                            ->placeholder("gmail@contoh.com | password123\ngmail@contoh2.com | password456\n...")
                            ->helperText('Satu item per baris. Setiap baris = 1 stok.'),
                    ])
                    ->action(function ($record, array $data) {
                        $lines = explode("\n", $data['items']);
                        $added = $record->addStockItems($lines);
                        \Filament\Notifications\Notification::make()
                            ->title('Stok Ditambahkan!')
                            ->body("{$added} item ditambahkan. Total stok: {$record->fresh()->stok}")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
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
