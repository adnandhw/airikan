<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\Transaction;
use BackedEnum;
use Filament\Forms; // Re-added Forms
use Filament\Schemas;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'short_id';

    public static function getGloballySearchableAttributes(): array
    {
        return ['short_id'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Schemas\Components\Group::make()
                    ->schema([
                        // 1. Header Card (ID & Status)
                        Schemas\Components\Section::make()
                            ->schema([
                                Schemas\Components\Grid::make(['default' => 1, 'sm' => 2])
                                    ->schema([
                                        Forms\Components\Placeholder::make('id_display')
                                            ->label('ID Transaksi')
                                            ->content(fn ($record) => '#' . $record->short_id)
                                            ->extraAttributes(['class' => 'text-xl font-bold']),
                                        
                                        Forms\Components\Select::make('status')
                                            ->options([
                                                'pending' => 'Pending',
                                                'paid' => 'Paid (Menunggu Konfirmasi)',
                                                'approve' => 'Approve (Selesai)',
                                                'reject' => 'Pembayaran Ditolak',
                                            ])
                                            ->native(false)
                                            ->selectablePlaceholder(false),
                                    ]),
                                    Forms\Components\Placeholder::make('created_at')
                                        ->hiddenLabel()
                                        ->content(fn ($record) => $record?->created_at?->translatedFormat('d F Y \p\u\k\u\l H.i') ?? '-'),
                            ]),

                        // 2. Payment Proof (MOVED UP FOR BETTER VISIBILITY)
                        Schemas\Components\Section::make('BUKTI PEMBAYARAN')
                            ->schema([
                                Forms\Components\Placeholder::make('payment_proof_display')
                                    ->hiddenLabel()
                                    ->content(fn ($record) => $record && $record->payment_proof 
                                        ? new \Illuminate\Support\HtmlString('<div style="text-align: center;"><a href="' . asset('storage/' . $record->payment_proof) . '" target="_blank"><img src="' . asset('storage/' . $record->payment_proof) . '" style="max-height: 250px; max-width: 100%; border-radius: 8px; border: 1px solid #ddd; margin: 0 auto;" /></a><br/><small class="text-gray-500">Klik gambar untuk memperbesar</small></div>') 
                                        : 'Belum ada bukti pembayaran')
                                    ->columnSpanFull(),
                            ])
                            ->collapsible(),

                        // 3. Info Pengiriman
                        Schemas\Components\Section::make('INFO PENGIRIMAN')
                            ->schema([
                                Schemas\Components\Grid::make(['default' => 1, 'sm' => 2])
                                    ->schema([
                                        Forms\Components\Placeholder::make('buyer_name')
                                            ->hiddenLabel()
                                            ->content(fn ($record) => $record->buyer_info['name'] ?? '-')
                                            ->extraAttributes(['class' => 'font-bold text-lg']),
                                            
                                        Forms\Components\Placeholder::make('buyer_phone')
                                            ->hiddenLabel()
                                            ->content(fn ($record) => $record->buyer_info['phone'] ?? '-')
                                            ->icon('heroicon-m-phone'),
                                    ]),
                                    
                                Forms\Components\Placeholder::make('buyer_address')
                                    ->hiddenLabel()
                                    ->content(fn ($record) => $record->buyer_info['address'] ?? '-')
                                    ->icon('heroicon-m-map-pin'),
                            ])
                            ->compact(),

                        // 4. Products Table (Includes Total)
                        Forms\Components\ViewField::make('products')
                            ->view('filament.forms.components.order-items')
                            ->hiddenLabel()
                            ->columnSpanFull(),


                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('short_id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('buyer_info.name')
                    ->label('Buyer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('buyer_info.phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('payment_proof')
                    ->label('Bukti')
                    ->disk('public')
                    ->visibility('public')
                    ->square(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'paid' => 'Paid (Menunggu Konfirmasi)',
                        'approve' => 'Approve (Selesai)',
                        'reject' => 'Pembayaran Ditolak',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'info',
                        'approve' => 'success',
                        'reject' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => ListTransactions::route('/'),
            // 'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }
}
