<?php

namespace App\Filament\Resources\DigitalProducts\Pages;

use App\Filament\Resources\DigitalProducts\DigitalProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDigitalProducts extends ListRecords
{
    protected static string $resource = DigitalProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Produk'),
        ];
    }
}
