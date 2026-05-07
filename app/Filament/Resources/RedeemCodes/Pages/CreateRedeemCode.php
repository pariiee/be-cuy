<?php

namespace App\Filament\Resources\RedeemCodes\Pages;

use App\Filament\Resources\RedeemCodes\RedeemCodeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRedeemCode extends CreateRecord
{
    protected static string $resource = RedeemCodeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
