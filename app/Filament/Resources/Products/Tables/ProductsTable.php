<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Име')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Цена')
                    ->placeholder('-')
                    ->money('EUR', 0.00)
                    ->sortable()
                    ->visible(fn ($record): bool => ($record->variants_count ?? 0) > 0),
                TextColumn::make('sale_price')
                    ->label('Цена с отстъпка')
                    ->placeholder('-')
                    ->money('EUR', 0.00)
                    ->sortable()
                    ->visible(fn ($record): bool => ($record->variants_count ?? 0) > 0),
                IconColumn::make('stock')
                    ->label('Наличност')
                    ->visible(fn ($record): bool => ($record->variants_count ?? 0) > 0),
                TextColumn::make('weight')
                    ->label('Тегло (кг)')
                    ->placeholder('-')
                    ->suffix('кг')
                    ->sortable()
                    ->visible(fn ($record): bool => ($record->variants_count ?? 0) > 0),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
