<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected static ?string $title = 'Продажби на място';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Нова продажба'),
        ];
    }
}
