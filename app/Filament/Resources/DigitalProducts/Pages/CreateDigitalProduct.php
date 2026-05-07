<?php

namespace App\Filament\Resources\DigitalProducts\Pages;

use App\Filament\Resources\DigitalProducts\DigitalProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDigitalProduct extends CreateRecord
{
    protected static string $resource = DigitalProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
