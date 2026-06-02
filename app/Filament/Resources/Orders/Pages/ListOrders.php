<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public ?string $ordersTableSnapshot = null;

    public function mount(): void
    {
        parent::mount();

        $this->ordersTableSnapshot = $this->resolveOrdersTableSnapshot();
    }

    public function refreshOrdersTable(): void
    {
        $snapshot = $this->resolveOrdersTableSnapshot();

        if ($snapshot === $this->ordersTableSnapshot) {
            return;
        }

        $this->ordersTableSnapshot = $snapshot;
        $this->flushCachedTableRecords();
        $this->dispatch('refresh-sidebar');
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    private function resolveOrdersTableSnapshot(): string
    {
        $query = OrderResource::getEloquentQuery();

        return implode(':', [
            (clone $query)->count(),
            (clone $query)->max('id') ?? 0,
            (clone $query)->max('updated_at') ?? '',
        ]);
    }
}
