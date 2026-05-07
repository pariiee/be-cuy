<?php

namespace App\Filament\Resources\QrisFees\Pages;

use App\Filament\Resources\QrisFees\QrisFeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQrisFee extends EditRecord
{
    protected static string $resource = QrisFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
