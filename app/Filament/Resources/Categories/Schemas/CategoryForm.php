<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->disk('public') // 🔑 wajib
                    ->directory('category')
                    ->visibility('public')
                    ->imagePreviewHeight('150')
                    ->loadingIndicatorPosition('left')
                    ->removeUploadedFileButtonPosition('right')
                    ->required(),

                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        // auto slug
                        $set('slug', Str::slug($state));
                    }),

                // 🔽 slug disimpan tapi tidak ditampilkan
                Forms\Components\Hidden::make('slug')
                    ->required(),
            ]);
    }
}
