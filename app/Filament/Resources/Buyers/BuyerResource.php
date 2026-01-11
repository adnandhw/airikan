<?php

namespace App\Filament\Resources\Buyers;

use App\Filament\Resources\Buyers\Pages\CreateBuyer;
use App\Filament\Resources\Buyers\Pages\EditBuyer;
use App\Filament\Resources\Buyers\Pages\ListBuyers;
use App\Filament\Resources\Buyers\Schemas\BuyerForm;
use App\Filament\Resources\Buyers\Tables\BuyersTable;
use App\Models\Buyer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BuyerResource extends Resource
{
    protected static ?string $model = Buyer::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getGlobalSearchEloquentQuery();
        $referer = request()->header('Referer');
        if (!$referer) return $query;
        
        $path = parse_url($referer, PHP_URL_PATH);

        if (str_contains($path, '/admin/buyers') && static::class !== self::class) return $query->where('name', 'impossible_value');
        if (str_contains($path, '/admin/products') && static::class !== \App\Filament\Resources\Products\ProductResource::class) return $query->where('name', 'impossible_value');
        if (str_contains($path, '/admin/categories') && static::class !== \App\Filament\Resources\Categories\CategoryResource::class) return $query->where('name', 'impossible_value');
        if (str_contains($path, '/admin/banners') && static::class !== \App\Filament\Resources\Banners\BannerResource::class) return $query->where('name', 'impossible_value');

        return $query;
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function form(Schema $schema): Schema
    {
        return BuyerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BuyersTable::configure($table);
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
            'index' => ListBuyers::route('/'),
            'create' => CreateBuyer::route('/create'),
            'edit' => EditBuyer::route('/{record}/edit'),
        ];
    }
}
