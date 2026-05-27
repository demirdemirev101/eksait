<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Категория')
                    ->searchable(),
                TextColumn::make('parent.name')
                    ->label('Родител')
                    ->placeholder('-'),
                TextColumn::make('slug')
                    ->label('Кратък адрес')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('parent_id')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Редактирай'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
