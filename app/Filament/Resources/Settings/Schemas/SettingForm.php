<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Доставка')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('delivery_enabled')
                                ->label('Активна доставка')
                                ->default(true)
                                ->reactive(),
                            TextInput::make('free_delivery_over')
                                ->label('Безплатна доставка над')
                                ->numeric()
                                ->prefix('EUR ')
                                ->nullable()
                                ->helperText('Остави празно, ако няма безплатна доставка.')
                                ->disabled(fn ($get) => $get('delivery_enabled') === false),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
