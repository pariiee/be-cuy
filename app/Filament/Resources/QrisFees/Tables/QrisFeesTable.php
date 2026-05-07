<?php

namespace App\Filament\Resources\QrisFees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QrisFeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purpose')
                    ->label('Tujuan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'deposit' => 'Deposit',
                        'transaction' => 'Transaksi',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'deposit' => 'info',
                        'transaction' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('fee_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fixed' => 'Nominal Tetap',
                        'percentage' => 'Persentase',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'warning',
                        'percentage' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('fee_value')
                    ->label('Nilai')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->fee_type === 'percentage') {
                            return $state . '%';
                        }
                        return 'Rp' . number_format($state, 0, ',', '.');
                    }),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
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
            ->defaultSort('purpose');
    }
}
