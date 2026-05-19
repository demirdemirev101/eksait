<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_name')
                    ->label('Име на клиента')
                    ->disabled(),

                TextInput::make('customer_email')
                    ->label('Имейл')
                    ->disabled(),

                TextInput::make('customer_phone')
                    ->label('Телефон')
                    ->tel()
                    ->disabled(),

                Select::make('shipping_method')
                    ->label('Метод на доставка')
                    ->options([
                        'address' => 'До адрес',
                        'office' => 'До офис',
                        'apm' => 'До Еконтомат',
                    ])
                    ->native(false)
                    ->disabled(),

                TextInput::make('shipping_city')
                    ->label('Град за доставка')
                    ->required()
                    ->disabled(),

                TextInput::make('shipping_address')
                    ->label('Адрес за доставка')
                    ->visible(fn ($get) => $get('shipping_method') === 'address')
                    ->disabled(),

                TextInput::make('shipping_postcode')
                    ->label('Пощенски код')
                    ->visible(fn ($get) => $get('shipping_method') === 'address')
                    ->required(fn ($get) => $get('shipping_method') === 'address')
                    ->disabled(),

                TextInput::make('econt_office_name')
                    ->label('Име на офис / Еконтомат')
                    ->visible(fn ($get) => $get('shipping_method') !== 'address')
                    ->disabled(),

                TextInput::make('econt_office_address')
                    ->label('Адрес на офис / Еконтомат')
                    ->visible(fn ($get) => $get('shipping_method') !== 'address')
                    ->disabled(),

                Select::make('status')
                    ->label('Статус на поръчката')
                    ->options(
                        collect(OrderStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                            ->toArray()
                    )
                    ->required()
                    ->default(OrderStatus::PENDING->value)
                    ->native(false)
                    ->preload()
                    ->disabled(),

                TextInput::make('subtotal')
                    ->label('Междинна сума')
                    ->numeric()
                    ->prefix('EUR ')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('shipping_price')
                    ->label('Цена за доставка')
                    ->prefix('EUR ')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('total')
                    ->label('Обща сума')
                    ->numeric()
                    ->prefix('EUR ')
                    ->disabled()
                    ->dehydrated(false),

                Select::make('payment_method')
                    ->label('Метод на плащане')
                    ->options(
                        collect(PaymentMethod::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                            ->toArray()
                    )
                    ->required()
                    ->default(PaymentMethod::COD->value)
                    ->native(false)
                    ->preload()
                    ->disabled(),

                Select::make('payment_status')
                    ->label('Статус на плащане')
                    ->options(
                        collect(PaymentStatus::cases())
                            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                            ->toArray()
                    )
                    ->required()
                    ->default(PaymentStatus::PENDING->value)
                    ->native(false)
                    ->preload()
                    ->disabled(),

                Textarea::make('notes')
                    ->label('Бележки')
                    ->disabled(),
            ]);
    }
}
