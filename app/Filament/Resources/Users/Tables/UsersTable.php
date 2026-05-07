<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('balance')
                    ->label('Saldo')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin'    => 'danger',
                        'reseller' => 'warning',
                        'member'   => 'info',
                        default    => 'gray',
                    }),
                IconColumn::make('is_banned')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'admin'    => 'Admin',
                        'reseller' => 'Reseller',
                        'member'   => 'Member',
                    ]),
                TernaryFilter::make('is_banned')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Banned')
                    ->falseLabel('Aktif'),
            ])
            ->recordActions([
                Action::make('editBalance')
                    ->label('Edit Saldo')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning')
                    ->modalHeading(fn (User $record): string => 'Edit Saldo: ' . $record->name)
                    ->modalSubmitActionLabel('Simpan')
                    ->form([
                        TextInput::make('balance')
                            ->label('Saldo Baru (Rp)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('Rp'),
                    ])
                    ->fillForm(fn (User $record): array => [
                        'balance' => (float) $record->balance,
                    ])
                    ->action(function (array $data, User $record): void {
                        $record->update(['balance' => $data['balance']]);
                    }),
                Action::make('toggleBan')
                    ->label(fn (User $record): string => $record->is_banned ? 'Unban' : 'Ban')
                    ->icon(fn (User $record): string => $record->is_banned ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (User $record): string => $record->is_banned ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => $record->is_banned ? 'Unban User?' : 'Ban User?')
                    ->modalDescription(fn (User $record): string => $record->is_banned
                        ? 'User ' . $record->name . ' akan diaktifkan kembali.'
                        : 'User ' . $record->name . ' tidak akan bisa login setelah dibanned.')
                    ->action(function (User $record): void {
                        $record->update([
                            'is_banned'  => ! $record->is_banned,
                            'ban_reason' => $record->is_banned ? null : 'Dibanned oleh admin',
                        ]);
                    })
                    ->visible(fn (User $record): bool => $record->role !== 'admin'),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => $record->role !== 'admin'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
