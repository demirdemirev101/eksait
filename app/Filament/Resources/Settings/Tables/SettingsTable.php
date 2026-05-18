<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('delivery_enabled')
                    ->label('Активна доставка')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('free_delivery_over')
                    ->label('Безплатна доставка над')
                    ->money('EUR', 0.00),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->label('Редактирай'),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
