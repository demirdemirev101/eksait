<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Име')
                    ->required(),

                Select::make('category_id')
                    ->label('Категории')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload()
                    ->required(),

                TextInput::make('slug')
                    ->hidden()
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                RichEditor::make('description')
                    ->label('Описание')
                    ->columnSpanFull(),

                Section::make('Цени, тегло, размери и наличност')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('price')
                                ->label('Цена')
                                ->numeric()
                                ->prefix('EUR '),
                            TextInput::make('sale_price')
                                ->label('Цена с отстъпка')
                                ->numeric()
                                ->prefix('EUR '),
                            TextInput::make('weight')
                                ->label('Тегло (кг)')
                                ->numeric()
                                ->suffix('кг'),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('height')
                                ->label('Височина (см)')
                                ->numeric()
                                ->minValue(0.01),
                            TextInput::make('width')
                                ->label('Ширина (см)')
                                ->numeric()
                                ->minValue(0.01),
                            TextInput::make('length')
                                ->label('Дължина (см)')
                                ->numeric()
                                ->minValue(0.01),
                        ]),

                        Toggle::make('stock')
                            ->label('Следи наличност')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set): void {
                                if ($state === false) {
                                    $set('quantity', null);
                                }
                            }),

                        TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->visible(fn ($get) => $get('stock') === true),
                    ])
                    ->columnSpanFull(),

                RichEditor::make('extra_information')
                    ->label('Допълнителна информация')
                    ->columnSpanFull(),
            ]);
    }
}

