<?php

namespace App\Filament\Resources\Banners;

use App\Filament\Resources\Banners\Pages\CreateBanner;
use App\Filament\Resources\Banners\Pages\EditBanner;
use App\Filament\Resources\Banners\Pages\ListBanners;
use App\Filament\Resources\Banners\Schemas\BannerForm;
use App\Filament\Resources\Banners\Tables\BannersTable;
use App\Models\Banner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getGlobalSearchEloquentQuery();
        $referer = request()->header('Referer');
        if (!$referer) return $query;
        
        $path = parse_url($referer, PHP_URL_PATH);

        if (str_contains($path, '/admin/buyers') && static::class !== \App\Filament\Resources\Buyers\BuyerResource::class) return $query->where('id', 'impossible');
        if (str_contains($path, '/admin/products') && static::class !== \App\Filament\Resources\Products\ProductResource::class) return $query->where('id', 'impossible');
        if (str_contains($path, '/admin/categories') && static::class !== \App\Filament\Resources\Categories\CategoryResource::class) return $query->where('id', 'impossible');
        if (str_contains($path, '/admin/banners') && static::class !== self::class) return $query->where('id', 'impossible');

        return $query;
    }

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return BannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BannersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBanners::route('/'),
            'create' => CreateBanner::route('/create'),
            'edit' => EditBanner::route('/{record}/edit'),
        ];
    }
}

