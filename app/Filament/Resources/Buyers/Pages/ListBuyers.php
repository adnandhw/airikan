<?php

namespace App\Filament\Resources\Buyers\Pages;

use App\Filament\Resources\Buyers\BuyerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuyers extends ListRecords
{
    protected static string $resource = BuyerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
