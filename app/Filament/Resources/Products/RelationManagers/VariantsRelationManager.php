<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Illuminate\Database\Eloquent\Model;
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

    protected static ?string $title = 'Variants';
    protected static ?string $modelLabel = 'variant';
    protected static ?string $pluralModelLabel = 'variants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic data')
                    ->schema([
                        Grid::make(1)->schema([
                            TextInput::make('size')
                                ->label('Type / size')
                                ->required()
                                ->maxLength(255),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('price')
                                ->label('Price')
                                ->numeric()
                                ->prefix('EUR')
                                ->required(),
                            TextInput::make('sale_price')
                                ->label('Sale price')
                                ->numeric()
                                ->prefix('EUR')
                                ->nullable(),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Stock')
                    ->schema([
                        Grid::make(1)->schema([
                            TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->integer()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Dimensions and weight')
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('weight')
                                ->label('Weight')
                                ->numeric()
                                ->suffix('kg')
                                ->nullable(),
                            TextInput::make('height')
                                ->label('Height')
                                ->numeric()
                                ->suffix('cm')
                                ->nullable(),
                            TextInput::make('width')
                                ->label('Width')
                                ->numeric()
                                ->suffix('cm')
                                ->nullable(),
                            TextInput::make('length')
                                ->label('Length')
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
                    ->label('Type / size')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Price')
                    ->money('EUR')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('sale_price')
                    ->label('Sale price')
                    ->money('EUR')
                    ->visible(fn ($record): bool => filled($record?->sale_price))
                    ->sortable(),
                IconColumn::make('stock')
                    ->label('In stock')
                    ->boolean(),
                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable(),
                TextColumn::make('weight')
                    ->label('Weight')
                    ->suffix(' kg')
                    ->visible(fn ($record): bool => filled($record?->weight))
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create variant'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit'),
                DeleteAction::make()
                    ->label('Delete'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete selected'),
                ]),
            ]);
    }
}
