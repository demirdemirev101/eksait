<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основни данни')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Име')
                                ->required(),

                            Select::make('parent_id')
                                ->label('Родителска категория')
                                ->relationship('parent', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable(),
                        ]),

                        TextInput::make('slug')
                            ->label('Кратък адрес')
                            ->dehydrated()
                            ->hidden()
                            ->disabled()
                            ->required(),
                    ])
                    ->columnSpanFull(),

                Section::make('Преводи')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name_en')
                                ->label('Име на английски')
                                ->required(),

                            TextInput::make('name_de')
                                ->label('Име на немски')
                                ->required(),
                        ]),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
