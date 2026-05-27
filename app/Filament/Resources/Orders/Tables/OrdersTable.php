<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->columns([
                TextColumn::make('user_id')
                    ->label('Тип')
                    ->state(fn ($record) => $record->user_id ? 'Профил' : 'Гост')
                    ->badge()
                    ->color(fn ($state) => $state === 'Профил' ? 'success' : 'warning'),
                TextColumn::make('customer_name')
                    ->label('Име на клиента')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->label('Телефонен номер')
                    ->searchable()
                    ->visible(false),
                TextColumn::make('shipping_method')
                    ->label('Доставка')
                    ->state(fn ($record) => match ($record->shipping_method) {
                        'address' => 'До адрес',
                        'office' => 'До офис',
                        'apm' => 'До Еконтомат',
                        default => $record->shipping_method,
                    })
                    ->badge()
                    ->color(fn ($record) => match ($record->shipping_method) {
                        'address' => 'gray',
                        'office' => 'info',
                        'apm' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('shipping_destination')
                    ->label('Получаване')
                    ->state(fn ($record) => $record->shipping_method === 'address'
                        ? trim(implode(', ', array_filter([
                            $record->shipping_address,
                            $record->shipping_city,
                        ])))
                        : ($record->econt_office_name ?: $record->econt_office_code)
                    )
                    ->wrap()
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($subQuery) use ($search) {
                            $subQuery
                                ->where('shipping_address', 'like', "%{$search}%")
                                ->orWhere('shipping_city', 'like', "%{$search}%")
                                ->orWhere('econt_office_name', 'like', "%{$search}%")
                                ->orWhere('econt_office_code', 'like', "%{$search}%");
                        });
                    })
                    ->visible(false),
                TextColumn::make('status')
                    ->label('Статус на поръчката')
                    ->state(fn ($record) => OrderStatus::tryFrom($record->status)?->label() ?? $record->status)
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        'pending', 'pending_review' => 'warning',
                        'processing' => 'info',
                        'ready_for_shipment', 'shipped' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'return_requested' => 'warning',
                        'returned' => 'secondary',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Обща сума')
                    ->money('EUR', 0.00)
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Статус на плащане')
                    ->state(fn ($record) => PaymentStatus::tryFrom($record->payment_status)?->label() ?? $record->payment_status)
                    ->badge()
                    ->color(fn ($record) => match ($record->payment_status) {
                        'pending' => 'warning',
                        'unpaid' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'partially_refunded' => 'warning',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Метод на плащане')
                    ->state(fn ($record) => match ($record->payment_method) {
                        'cod' => 'Наложен платеж',
                        'bank_transfer' => 'Банков превод',
                        'stripe' => 'Карта (Stripe)',
                        default => $record->payment_method,
                    })
                    ->badge()
                    ->color(fn ($record) => match ($record->payment_method) {
                        'cod' => 'info',
                        'bank_transfer' => 'primary',
                        'stripe' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Дата на създаване')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Дата на актуализиране')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->status === OrderStatus::CANCELLED->value),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorize(fn (? \Illuminate\Database\Eloquent\Model $record) => $record
                            ? (($record->status === 'pending_review'
                                    || ($record->payment_method === 'bank_transfer' && $record->payment_status !== 'paid')))
                            : true),
                ]),
            ]);
    }
}
