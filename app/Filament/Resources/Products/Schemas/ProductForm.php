<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Category;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([

            Grid::make(2)->schema([

                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(150),

                Select::make('category_id')
                    ->label('Kategori')
                    ->options(
                        Category::query()
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('type', null)),

                Select::make('type')
                    ->label('Jenis / Varian')
                    ->options(function ($get) {
                        $categoryId = $get('category_id');
                        if (!$categoryId) {
                            return [];
                        }
                        $category = \App\Models\Category::find($categoryId);
                        if (!$category || empty($category->types)) {
                            return [];
                        }
                        $types = $category->types;
                        
                        if (empty($types)) {
                            return [];
                        }

                        if (is_string($types)) {
                            $decoded = json_decode($types, true);
                            
                            if (is_array($decoded)) {
                                $types = $decoded;
                            } else {
                                $clean = trim($types, '"\'');
                                if (str_contains($clean, ',')) {
                                    $types = array_map('trim', explode(',', $clean));
                                } else {
                                    $types = [$clean];
                                }
                            }
                        }
                        
                        if (!is_array($types)) {
                             return [];
                        }

                        $types = array_filter($types);
                        if (empty($types)) return [];
                        
                        return array_combine($types, $types);
                    })
                    ->searchable()
                    ->required()
                    ->live(),

                TextInput::make('price')
                    ->label('Harga')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                TextInput::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->minValue(0)
                    ->required(),

                FileUpload::make('image')
                    ->label('Foto Produk')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp', 'image/svg+xml', 'application/octet-stream'])
                    ->disk('public')
                    ->directory('products')
                    ->imagePreviewHeight('150')
                    ->required(),

                TextInput::make('size')
                    ->label('Ukuran')
                    ->placeholder('Contoh: 15cm')
                    ->maxLength(50),

                TextInput::make('weight')
                    ->label('Berat (Gram)')
                    ->numeric()
                    ->placeholder('Contoh: 1000')
                    ->suffix('Gram')
                    ->minValue(0)
                    ->required()
                    ->default(1000),

            ]),

            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3)
                ->columnSpanFull(),

            Section::make('Diskon Produk')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('discount_percentage')
                            ->label('Persentase Diskon (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50)
                            ->suffix('%')
                            ->placeholder('0 - 50'),

                        TextInput::make('discount_duration')
                            ->label('Durasi Diskon (Hari)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(30)
                            ->suffix('Hari')
                            ->placeholder('1 - 30'),
                    ]),
                ])
                ->collapsible(),

        ]);
    }
}
