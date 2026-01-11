<?php

namespace App\Filament\Resources\ProductResellers\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class ProductResellersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // Nama Produk
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),

                // Kategori
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),

                // Jenis (from Product)
                Tables\Columns\TextColumn::make('product.type') // Assuming product has type
                    ->label('Jenis')
                    ->badge(),

                // Harga
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', true)
                    ->sortable(),

                // Stok (Sync with Parent Product if exists)
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return $record->product ? $record->product->stock : $record->stock;
                    })
                    ->color(fn (int $state) => $state > 0 ? 'success' : 'danger'),

                // Created
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable(),
            ])

            ->filters([])

            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
