<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Варианти';
    protected static ?string $modelLabel = 'вариант';
    protected static ?string $pluralModelLabel = 'варианти';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основни данни')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('size')
                                ->label('Размер')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('price')
                                ->label('Цена')
                                ->numeric()
                                ->prefix('EUR')
                                ->required(),
                            TextInput::make('sale_price')
                                ->label('Цена с отстъпка')
                                ->numeric()
                                ->prefix('EUR')
                                ->nullable(),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Наличност')
                    ->schema([
                        Grid::make(1)->schema([
                            TextInput::make('quantity')
                                ->label('Количество')
                                ->numeric()
                                ->integer()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Размери и тегло')
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('weight')
                                ->label('Тегло')
                                ->numeric()
                                ->suffix('kg')
                                ->nullable(),
                            TextInput::make('height')
                                ->label('Височина')
                                ->numeric()
                                ->suffix('cm')
                                ->nullable(),
                            TextInput::make('width')
                                ->label('Ширина')
                                ->numeric()
                                ->suffix('cm')
                                ->nullable(),
                            TextInput::make('length')
                                ->label('Дължина')
                                ->numeric()
                                ->suffix('cm')
                                ->nullable(),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('size')
            ->columns([
                TextColumn::make('size')
                    ->label('Размер')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Цена')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('sale_price')
                    ->label('Цена с отстъпка')
                    ->money('EUR')
                    ->placeholder('-')
                    ->sortable(),
                IconColumn::make('stock')
                    ->label('Наличнот')
                    ->boolean(),
                TextColumn::make('quantity')
                    ->label('Количество')
                    ->sortable(),
                TextColumn::make('weight')
                    ->label('Тегло')
                    ->suffix(' kg')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Създай вариант'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Редактирай'),
                DeleteAction::make()
                    ->label('Изтрий'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Изтрий избраните'),
                ]),
            ]);
    }
}
