<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static BackedEnum|string|null $navigationIcon =
        'heroicon-o-square-3-stack-3d';

    protected static UnitEnum|string|null $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    // Judul record di UI
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getGlobalSearchEloquentQuery();
        $referer = request()->header('Referer');
        if (!$referer) return $query;
        
        $path = parse_url($referer, PHP_URL_PATH);

        if (str_contains($path, '/admin/buyers') && static::class !== \App\Filament\Resources\Buyers\BuyerResource::class) return $query->where('id', 'impossible');
        if (str_contains($path, '/admin/products') && static::class !== \App\Filament\Resources\Products\ProductResource::class) return $query->where('id', 'impossible');
        if (str_contains($path, '/admin/categories') && static::class !== self::class) return $query->where('id', 'impossible');
        if (str_contains($path, '/admin/banners') && static::class !== \App\Filament\Resources\Banners\BannerResource::class) return $query->where('id', 'impossible');

        return $query;
    }

    /* ===================== FORM ===================== */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\FileUpload::make('image')
                ->label('Category Image')
                ->image()
                ->directory('category')
                ->disk('public')
                ->visibility('public')
                ->required(),

            Forms\Components\TextInput::make('name')
                ->label('Category Name')
                ->required()
                ->maxLength(100)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set) {
                    $set('slug', Str::slug($state));
                }),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->disabled()
                ->dehydrated(),

            Forms\Components\TagsInput::make('types')
                ->label('Jenis / Varian Produk')
                ->placeholder('Ketiga jenis baru dan tekan Enter')
                ->separator(',')
                ->splitKeys(['Tab', ','])
                ->columnSpanFull(),

        ]);
    }

    /* ===================== TABLE ===================== */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([

                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('products.type')
                    ->label('Jenis')
                    ->badge()
                    ->separator(',')
                    ->limitList(5)
                    ->state(function ($record) {
                        return $record->products->pluck('type')->unique()->filter()->values()->all();
                    }),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->copyable(),

            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
