<?php

namespace App\Filament\Resources\ProductMarkups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductMarkupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'okeconnect' => 'success',
                        'smmpanel' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('markup_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'warning',
                        'percentage' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('markup_value')
                    ->label('Nilai')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->markup_type === 'percentage') {
                            return $state . '%';
                        }
                        return 'Rp' . number_format($state, 0, ',', '.');
                    }),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('provider')
                    ->options([
                        'okeconnect' => 'OkeConnect',
                        'smmpanel' => 'SMM Panel',
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
            ->defaultSort('updated_at', 'desc');
    }
}
