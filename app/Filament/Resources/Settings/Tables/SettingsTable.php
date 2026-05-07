<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Keterangan')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('value')
                    ->label('Nilai')
                    ->formatStateUsing(function ($record) {
                        if ($record->key === 'qris_fee_type') {
                            return $record->value === 'fixed' ? 'Fixed (Rupiah)' : 'Percentage (%)';
                        }
                        if ($record->key === 'qris_fee_value') {
                            $feeType = \App\Models\Setting::getValue('qris_fee_type', 'fixed');
                            return $feeType === 'fixed'
                                ? 'Rp' . number_format((float) $record->value, 0, ',', '.')
                                : $record->value . '%';
                        }
                        if ($record->key === 'qris_enabled') {
                            return filter_var($record->value, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Nonaktif';
                        }
                        return $record->value;
                    })
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->key === 'qris_enabled' && filter_var($record->value, FILTER_VALIDATE_BOOLEAN) => 'success',
                        $record->key === 'qris_enabled' && !filter_var($record->value, FILTER_VALIDATE_BOOLEAN) => 'danger',
                        default => 'info',
                    }),
                TextColumn::make('group')
                    ->label('Grup')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'payment' => 'Pembayaran',
                        'general' => 'Umum',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'payment' => 'warning',
                        'general' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label('Grup')
                    ->options([
                        'general' => 'Umum',
                        'payment' => 'Pembayaran',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Ubah'),
            ]);
    }
}
