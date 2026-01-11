<?php

namespace App\Filament\Resources\ProductResellers\Pages;

use App\Filament\Resources\ProductResellers\ProductResellerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductReseller extends EditRecord
{
    protected static string $resource = ProductResellerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
