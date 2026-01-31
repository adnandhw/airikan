<?php

namespace App\Filament\Resources\ProductResellers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;

class ProductResellerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([

            Grid::make(2)->schema([

                TextInput::make('name')
                    ->label('Nama Produk (Sinkron dari Utama)')
                    ->disabled()
                    ->dehydrated(false)
                    ->required()
                    ->maxLength(150),

                Select::make('category_id')
                    ->label('Kategori (Sinkron dari Utama)')
                    ->options(
                        Category::query()
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->disabled()
                    ->dehydrated(false)
                    ->required(),

                \Filament\Forms\Components\Toggle::make('is_active')
                    ->label('Tampilkan di Website?')
                    ->default(true)
                    ->columnSpan(2),

                Select::make('product_id')
                    ->label('Parent Product (Otomatis)')
                    ->options(
                        Product::query()
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('type')
                    ->label('Jenis / Varian (Sinkron)')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Plakat Fancy, Halfmoon, dll')
                    ->required(),

                TextInput::make('price')
                    ->label('Harga (Sinkron)')
                    ->disabled()
                    ->dehydrated(false)
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                TextInput::make('stock')
                    ->label('Stok (Sinkron)')
                    ->disabled()
                    ->dehydrated(false)
                    ->numeric()
                    ->minValue(0)
                    ->required(),

                FileUpload::make('image')
                    ->label('Foto Produk (Sinkron)')
                    ->disabled()
                    ->dehydrated(false)
                    ->disk('public')
                    ->directory('product_resellers')
                    ->imagePreviewHeight('150')
                    ->required(),

                TextInput::make('size')
                    ->label('Ukuran (Sinkron)')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Contoh: 15cm')
                    ->maxLength(50),

                Forms\Components\Repeater::make('tier_pricing')
                    ->label('Atur Harga Berdasarkan Jumlah')
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Min. Jumlah (Qty)')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('discount_percentage')
                            ->label('Diskon (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set, ?string $state) {
                                $basePrice = (float) $get('../../price');
                                if ($basePrice > 0 && $state !== null) {
                                    $discountAmount = $basePrice * ($state / 100);
                                    $set('unit_price', $basePrice - $discountAmount);
                                }
                            }),

                        Forms\Components\TextInput::make('unit_price')
                            ->label('Harga Satuan (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set, ?string $state) {
                                $basePrice = (float) $get('../../price');
                                if ($basePrice > 0 && $state !== null) {
                                    $percentage = (($basePrice - $state) / $basePrice) * 100;
                                    $set('discount_percentage', round($percentage, 2));
                                }
                            }),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->columnSpanFull()
                    ->addActionLabel('Tambah Tier Harga'),

            ]),

            Textarea::make('description')
                ->label('Deskripsi (Sinkron)')
                ->disabled()
                ->dehydrated(false)
                ->rows(3)
                ->columnSpanFull(),





        ]);
    }
}
