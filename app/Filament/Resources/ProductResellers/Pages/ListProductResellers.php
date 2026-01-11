<?php

namespace App\Filament\Resources\ProductResellers\Pages;

use App\Filament\Resources\ProductResellers\ProductResellerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductResellers extends ListRecords
{
    protected static string $resource = ProductResellerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }
}
