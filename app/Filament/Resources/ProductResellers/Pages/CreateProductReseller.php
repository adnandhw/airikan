<?php

namespace App\Filament\Resources\ProductResellers\Pages;

use App\Filament\Resources\ProductResellers\ProductResellerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductReseller extends CreateRecord
{
    protected static string $resource = ProductResellerResource::class;
}
