<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public function createAnother(): void
    {
        parent::createAnother();

        $this->redirect(self::getResource()::getUrl('create'));
    }
}
