<?php

namespace App\Filament\Resources\Sales;

use App\Filament\Resources\Sales\Pages\CreateSale;
use App\Filament\Resources\Sales\Pages\ListSales;
use App\Models\Order;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SaleResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Продажби';
    protected static ?string $navigationLabel = 'Продажби на място';
    protected static ?string $modelLabel = 'продажба';
    protected static ?string $pluralModelLabel = 'Продажби';

    public static function canAccess(): bool
    {
        return Auth::user()?->can('view orders') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('view orders') ?? false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('source', 'panel')
            ->with('items');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Добавяне на продукт')
                    ->description('Изберете продукт, въведете количество и го добавете към текущата продажба.')
                    ->schema([
                        Select::make('product_id')
                            ->label('Продукт')
                            ->options(fn (): array => Product::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->dehydrated(false)
                            ->columnSpan(['default' => 12, 'lg' => 7]),

                        TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->dehydrated(false)
                            ->columnSpan(['default' => 12, 'sm' => 6, 'lg' => 2]),

                        Actions::make([
                            Action::make('add_to_cart')
                                ->label('Добави')
                                ->icon('heroicon-m-plus')
                                ->color('primary')
                                ->action(function ($livewire, $get, $set): void {
                                    $livewire->addToCart(
                                        productId: (int) $get('product_id'),
                                        quantity: (int) ($get('quantity') ?: 1),
                                    );

                                    $set('product_id', null);
                                    $set('quantity', 1);
                                }),
                        ])
                            ->verticalAlignment(VerticalAlignment::End)
                            ->columnSpan(['default' => 12, 'sm' => 6, 'lg' => 3]),
                    ])
                    ->columns(12)
                    ->columnSpanFull(),

                Section::make('Текуща продажба')
                    ->description('Прегледайте добавените продукти и крайната сума преди завършване.')
                    ->schema([
                        View::make('filament.resources.sales.cart-table')
                            ->viewData(fn ($livewire): array => [
                                'cart' => $livewire->cart,
                                'total' => $livewire->cartTotal(),
                            ])
                            ->key(fn ($livewire): string => 'cart-table-' . md5(json_encode($livewire->cart ?? [])))
                            ->columnSpanFull(),
                    ])
                    ->columns(12)
                    ->columnSpanFull(),

                Section::make('Плащане')
                    ->schema([
                        Select::make('payment_method')
                            ->label('Метод на плащане')
                            ->options([
                                'cash' => 'Кеш',
                                'card' => 'Карта',
                            ])
                            ->default('cash')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('items')
                    ->label('Артикули')
                    ->state(fn (Order $record): array => $record->items
                        ->map(fn ($item): string => "{$item->product_name} x {$item->quantity} - €" . number_format((float) $item->total, 2))
                        ->all())
                    ->bulleted()
                    ->listWithLineBreaks()
                    ->wrap(),

                TextColumn::make('payment_method')
                    ->label('Плащане')
                    ->state(fn (Order $record): string => match ($record->payment_method) {
                        'cash' => 'Кеш',
                        'card' => 'Карта',
                        default => $record->payment_method,
                    })
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->payment_method) {
                        'cash' => 'success',
                        'card' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->placeholder('Клиент на място')
                    ->searchable(),

                TextColumn::make('total')
                    ->label('Общо')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getNavigationUrl(): string
    {
        return static::getUrl('index');
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName() . '.*'))
                ->sort(static::getNavigationSort())
                ->url(static::getNavigationUrl()),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSales::route('/'),
            'create' => CreateSale::route('/create'),
        ];
    }
}
