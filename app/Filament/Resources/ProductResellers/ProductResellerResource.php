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
    protected static ?string $model = ProductReseller::class;

    protected static ?string $modelLabel = 'Reseller Product';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ProductResellerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductResellersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProductResellers::route('/'),
            'create' => CreateProductReseller::route('/create'),
            'edit'   => EditProductReseller::route('/{record}/edit'),
        ];
    }
}
