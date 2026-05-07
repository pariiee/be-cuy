<?php

namespace App\Filament\Resources\RedeemCodes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RedeemCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->color('primary'),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'discount'    => 'success',
                        'custom_text' => 'info',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'discount'    => 'Diskon',
                        'custom_text' => 'Custom Text',
                        default       => $state,
                    }),
                TextColumn::make('discount_value')
                    ->label('Nilai Diskon')
                    ->money('IDR', locale: 'id')
                    ->placeholder('—'),
                TextColumn::make('applicable_products')
                    ->label('Produk')
                    ->badge()
                    ->color('warning')
                    ->placeholder('Semua')
                    ->limit(3),
                TextColumn::make('used_count')
                    ->label('Terpakai')
                    ->formatStateUsing(function ($state, $record) {
                        $max = $record->max_usage > 0 ? $record->max_usage : '∞';
                        return "{$state} / {$max}";
                    })
                    ->color(fn ($record) => $record->max_usage > 0 && $record->used_count >= $record->max_usage ? 'danger' : 'success'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('valid_until')
                    ->label('Expired')
                    ->dateTime('d M Y')
                    ->placeholder('Tanpa Batas')
                    ->color(fn ($state) => $state && now()->gt($state) ? 'danger' : 'success'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'discount'    => 'Diskon',
                        'custom_text' => 'Custom Text',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
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
