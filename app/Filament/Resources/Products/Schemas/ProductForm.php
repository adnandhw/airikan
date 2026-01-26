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

                // Nama Produk
                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(150),

                // Kategori (RELASI MongoDB)
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

                // Jenis / Varian (Dependent Dropdown from Category embedded types)
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
                            
                            // If it decoded to an array, use it
                            if (is_array($decoded)) {
                                $types = $decoded;
                            } else {
                                // Otherwise treat as raw string (comma-separated or single)
                                // Remove quotes if present (sometimes happened in old migrations)
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

                        // Ensure values are strings and keys match values
                        $types = array_filter($types); // Remove empty
                        if (empty($types)) return [];
                        
                        return array_combine($types, $types);
                    })
                    ->searchable()
                    ->required()
                    ->live(),

                // Harga
                TextInput::make('price')
                    ->label('Harga')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                // Stok
                TextInput::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->minValue(0)
                    ->required(),

                // Upload banyak gambar
                // Upload gambar
                FileUpload::make('image')
                    ->label('Foto Produk')
                    ->image() // Helper to ensure it treats file as image
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml', 'image/tiff'])
                    ->disk('public')
                    ->directory('products')
                    ->imagePreviewHeight('150')
                    ->required(),

                // Ukuran (misal: 15cm)
                TextInput::make('size')
                    ->label('Ukuran')
                    ->placeholder('Contoh: 15cm')
                    ->maxLength(50),

            ]),

            // Deskripsi
            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3)
                ->columnSpanFull(),

            // Section Diskon
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
