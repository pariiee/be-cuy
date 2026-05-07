<?php

namespace App\Filament\Resources\RedeemCodes\Pages;

use App\Filament\Resources\RedeemCodes\RedeemCodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRedeemCodes extends ListRecords
{
    protected static string $resource = RedeemCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kode Redeem'),
        ];
    }
}
