<?php

namespace App\Filament\Resources\Orders;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Filament\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Orders\RelationManagers\ShipmentsRelationManager; // ← ДОБАВИ
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'Продажби';
    protected static ?string $navigationLabel = 'Поръчки през сайта';
    protected static ?string $pluralModelLabel = 'Поръчки';
    protected static ?string $modelLabel = 'Поръчка';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(fn (Builder $query) => $query
                ->where('source', '!=', 'panel')
                ->orWhereNull('source'));
    }

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            ShipmentsRelationManager::class, 
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getUnprocessedOrdersCount();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getUnprocessedOrdersCount() > 0 ? 'warning' : null;
    }

    protected static function getUnprocessedOrdersCount(): int
    {
        return static::getEloquentQuery()
            ->whereIn('status', [
                OrderStatus::PENDING->value,
                OrderStatus::PENDING_REVIEW->value,
                OrderStatus::PROCESSING->value,
            ])
            ->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
