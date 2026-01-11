<?php

namespace App\Filament\Resources\ProductResellers;

use App\Filament\Resources\ProductResellers\Pages\CreateProductReseller;
use App\Filament\Resources\ProductResellers\Pages\EditProductReseller;
use App\Filament\Resources\ProductResellers\Pages\ListProductResellers;
use App\Filament\Resources\ProductResellers\Schemas\ProductResellerForm;
use App\Filament\Resources\ProductResellers\Tables\ProductResellersTable;
use App\Models\ProductReseller;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class ProductResellerResource extends Resource
{
    // Model
    protected static ?string $model = ProductReseller::class;

    protected static ?string $modelLabel = 'Reseller Product';

    // Icon sidebar (Filament v3)
    // Icon sidebar (Filament v3)
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 4;

    // Title record
    protected static ?string $recordTitleAttribute = 'id';

    // Form
    public static function form(Schema $schema): Schema
    {
        return ProductResellerForm::configure($schema);
    }

    // Table
    public static function table(Table $table): Table
    {
        return ProductResellersTable::configure($table);
    }

    // Relations
    public static function getRelations(): array
    {
        return [];
    }

    // Pages
    public static function getPages(): array
    {
        return [
            'index'  => ListProductResellers::route('/'),
            'create' => CreateProductReseller::route('/create'),
            'edit'   => EditProductReseller::route('/{record}/edit'),
        ];
    }
}
