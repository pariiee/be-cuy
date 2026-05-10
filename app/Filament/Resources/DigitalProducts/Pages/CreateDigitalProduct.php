<?php

namespace App\Filament\Resources\DigitalProducts\Pages;

use App\Filament\Resources\DigitalProducts\DigitalProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDigitalProduct extends CreateRecord
{
    protected static string $resource = DigitalProductResource::class;

    protected function afterCreate(): void
    {
        $raw = $this->data['stok_items'] ?? '';
        if (filled($raw)) {
            $lines = explode("\n", $raw);
            $this->record->addStockItems($lines);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
