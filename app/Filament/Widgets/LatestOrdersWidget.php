<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrdersWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Последни поръчки')
            ->poll('5s')
            ->query(Order::query()->latest())
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10])
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->searchable()
                    ->limit(28),
                TextColumn::make('customer_phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->state(fn (Order $record): string => OrderStatus::tryFrom($record->status)?->label() ?? $record->status)
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->status) {
                        'pending', 'pending_review' => 'warning',
                        'processing' => 'info',
                        'ready_for_shipment', 'shipped' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'return_requested' => 'warning',
                        'returned' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('payment_status')
                    ->label('Плащане')
                    ->state(fn (Order $record): string => PaymentStatus::tryFrom($record->payment_status)?->label() ?? $record->payment_status)
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->payment_status) {
                        'pending', 'unpaid' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'partially_refunded' => 'warning',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('total')
                    ->label('Сума')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2, '.', ' ') . ' EUR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ]);
    }
}
