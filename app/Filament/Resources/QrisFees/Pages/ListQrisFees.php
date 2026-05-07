<?php

namespace App\Filament\Resources\QrisFees\Pages;

use App\Filament\Resources\QrisFees\QrisFeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQrisFees extends ListRecords
{
    protected static string $resource = QrisFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
