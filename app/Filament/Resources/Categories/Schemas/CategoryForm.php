<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Име')
                    ->required(),
                TextInput::make('name_en')
                    ->label('Име (EN)'),
                TextInput::make('name_de')
                    ->label('Име (DE)'),
                TextInput::make('slug')
                    ->label('Кратък адрес')
                    ->dehydrated()
                    ->hidden()
                    ->disabled()
                    ->required(),
                Select::make('parent_id')
                    ->label('Родителска категория')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }
}
