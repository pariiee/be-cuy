<?php

namespace App\Filament\Resources\DigitalProducts\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
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
                TextColumn::make('category.nama_kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('app_category')
                    ->label('Apps')
                    ->badge()
                    ->color('warning')
                    ->placeholder('—'),
                TextColumn::make('harga_user')
                    ->label('Harga User')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('harga_reseller')
                    ->label('Harga Reseller')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->color('success'),
                IconColumn::make('garansi')
                    ->label('Garansi')
                    ->boolean(),
                TextColumn::make('stok')
                    ->label('Stok')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state : 'Habis'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'nama_kategori'),
            ])
            ->recordActions([
                Action::make('restock')
                    ->label('Restock')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('tambah_stok')
                            ->label('Tambah Stok')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->placeholder('Jumlah stok yang ditambahkan')
                            ->helperText('Stok akan ditambahkan ke jumlah saat ini'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->restock((int) $data['tambah_stok']);
                    })
                    ->after(function ($record) {
                        \Filament\Notifications\Notification::make()
                            ->title('Restock Berhasil!')
                            ->body("Stok {$record->nama_produk} sekarang: {$record->fresh()->stok}")
                            ->success()
                            ->send();
                    }),
                Action::make('set_stok')
                    ->label('Set Stok')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        TextInput::make('stok')
                            ->label('Jumlah Stok Baru')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->placeholder('Atur jumlah stok secara manual'),
                    ])
                    ->fillForm(fn ($record) => ['stok' => $record->stok])
                    ->action(fn ($record, array $data) => $record->update(['stok' => (int) $data['stok']])),
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
