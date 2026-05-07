<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->nullable()
                    ->maxLength(20),
                Select::make('role')
                    ->label('Role')
                    ->options([
                        'admin'    => 'Admin',
                        'reseller' => 'Reseller',
                        'member'   => 'Member',
                    ])
                    ->required(),
                TextInput::make('balance')
                    ->label('Saldo (Rp)')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('password')
                    ->label('Password Baru')
                    ->password()
                    ->nullable()
                    ->minLength(8)
                    ->helperText('Kosongkan jika tidak ingin mengubah password'),
                Toggle::make('is_banned')
                    ->label('Banned')
                    ->default(false),
                TextInput::make('ban_reason')
                    ->label('Alasan Ban')
                    ->nullable()
                    ->maxLength(500)
                    ->visible(fn ($get): bool => (bool) $get('is_banned')),
            ]);
    }
}
