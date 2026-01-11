<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FileUpload::make('image')
                    ->label('Banner Image')
                    ->image()
                    ->directory('banner') // semua file akan masuk storage/app/public/banner
                    ->disk('public')
                    ->visibility('public')
                    ->required()
                    ->preserveFilenames() // agar nama file tidak berubah
                    ->maxSize(2048),
            ]);
    }
}
