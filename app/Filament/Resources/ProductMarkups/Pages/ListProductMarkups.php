<?php

namespace App\Filament\Resources\ProductMarkups\Pages;

use App\Filament\Resources\ProductMarkups\ProductMarkupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductMarkups extends ListRecords
{
    protected static string $resource = ProductMarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
