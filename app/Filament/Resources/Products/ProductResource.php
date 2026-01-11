<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class ProductResource extends Resource
{
    // Model
    protected static ?string $model = Product::class;

    // Icon sidebar (Filament v3)
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    // Group disamakan dengan Category & Banner
    protected static UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 3;

    // Title record
    // Title record
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getGlobalSearchEloquentQuery();
        $referer = request()->header('Referer');
        if (!$referer) return $query;
        
        $path = parse_url($referer, PHP_URL_PATH);

        if (str_contains($path, '/admin/buyers') && static::class !== \App\Filament\Resources\Buyers\BuyerResource::class) return $query->where('id', 'impossible');
        if (str_contains($path, '/admin/products') && static::class !== self::class) return $query->where('id', 'impossible');
        if (str_contains($path, '/admin/categories') && static::class !== \App\Filament\Resources\Categories\CategoryResource::class) return $query->where('id', 'impossible');
        if (str_contains($path, '/admin/banners') && static::class !== \App\Filament\Resources\Banners\BannerResource::class) return $query->where('id', 'impossible');

        return $query;
    }

    // Form
    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    // Table
    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    // Relations (kosong dulu)
    public static function getRelations(): array
    {
        return [];
    }

    // Pages
    public static function getPages(): array
    {
        return [
            'index'  => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit'   => EditProduct::route('/{record}/edit'),
        ];
    }
}
