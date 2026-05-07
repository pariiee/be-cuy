<?php

namespace App\Filament\Resources\ProductMarkups\Pages;

use App\Filament\Resources\ProductMarkups\ProductMarkupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductMarkup extends EditRecord
{
    protected static string $resource = ProductMarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
