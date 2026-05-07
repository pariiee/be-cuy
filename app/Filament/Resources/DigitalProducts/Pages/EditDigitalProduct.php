<?php

namespace App\Filament\Resources\DigitalProducts\Pages;

use App\Filament\Resources\DigitalProducts\DigitalProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDigitalProduct extends EditRecord
{
    protected static string $resource = DigitalProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
