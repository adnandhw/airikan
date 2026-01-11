<?php

namespace App\Filament\Resources\Buyers\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;

class BuyerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => \Illuminate\Support\Facades\Hash::make($state))
                    ->label('Password'),
                Forms\Components\Select::make('province_id')
                    ->label('Province')
                    ->searchable()
                    ->live()
                    ->options(fn () => \Laravolt\Indonesia\Models\Province::pluck('name', 'code'))
                    ->afterStateUpdated(fn ($set) => $set('city_id', null)),

                Forms\Components\Select::make('city_id')
                    ->label('Regency')
                    ->searchable()
                    ->live()
                    ->options(fn ($get) => $get('province_id') 
                        ? \Laravolt\Indonesia\Models\City::where('province_code', $get('province_id'))->pluck('name', 'code')
                        : [])
                    ->afterStateUpdated(fn ($set) => $set('district_id', null)),

                Forms\Components\Select::make('district_id')
                    ->label('District')
                    ->searchable()
                    ->live()
                    ->options(fn ($get) => $get('city_id') 
                        ? \Laravolt\Indonesia\Models\District::where('city_code', $get('city_id'))->pluck('name', 'code')
                        : [])
                    ->afterStateUpdated(fn ($set) => $set('village_id', null)),

                Forms\Components\Select::make('village_id')
                    ->label('Village')
                    ->searchable()
                    ->options(fn ($get) => $get('district_id') 
                        ? \Laravolt\Indonesia\Models\Village::where('district_code', $get('district_id'))->pluck('name', 'code')
                        : []),
                Forms\Components\Textarea::make('address')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('postal_code')
                    ->label('Postal Code'),
                Forms\Components\Select::make('reseller_status')
                    ->label('Reseller Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'reject' => 'Reject',
                    ])
                    ->default('pending'),
            ]);
    }
}
