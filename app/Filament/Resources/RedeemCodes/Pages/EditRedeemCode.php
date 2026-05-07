<?php

namespace App\Filament\Resources\RedeemCodes\Pages;

use App\Filament\Resources\RedeemCodes\RedeemCodeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRedeemCode extends EditRecord
{
    protected static string $resource = RedeemCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
