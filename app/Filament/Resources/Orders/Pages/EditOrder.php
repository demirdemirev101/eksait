<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderReadyForShipment;
use App\Filament\Resources\Orders\OrderResource;
use App\Policies\CancelOrderPolicy;
use App\Policies\ConfirmBankTransferPolicy;
use App\Policies\IsOrderLockedPolicy;
use App\Policies\ShipmentPollingPolicy;
use App\Services\OrderCancellationService;
use App\Services\OrderReturnRequestService;
use App\Services\Shipment\ShipmentTrackingSyncService;
use App\Services\StripeRefundService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected string $view = 'filament.resources.orders.pages.edit-order';

    public function handleOrderRefresh(): void
    {
        $this->getRecord()->refresh();

        $this->fillForm();
    }

    private function refreshUi(): void
    {
        $this->handleOrderRefresh();
        $this->dispatch('$refresh');
        $this->dispatch('orderUpdated');
    }

    public function shouldPollShipmentStatus(): bool
    {
        $record = $this->getRecord()->fresh(['shipment', 'returnShipment']);

        if (! $record) {
            return false;
        }

        return app(ShipmentPollingPolicy::class)->shouldPollShipmentStatus($record);
    }

    public function pollShipmentStatus(): void
    {
        if (property_exists($this, 'mountedActions') && ! empty($this->mountedActions)) {
            return;
        }

        $record = $this->getRecord();

        if (! $record) {
            return;
        }

        $changed = app(ShipmentTrackingSyncService::class)->syncShipmentTracking($record);

        if ($changed) {
            $this->refreshUi();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm_cod')
                ->label('Потвърди поръчка (наложен платеж)')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->visible(fn () => $this->record->payment_method === 'cod'
                    && $this->record->status === 'pending_review')
                ->requiresConfirmation()
                ->modalHeading('Потвърждаване на поръчка')
                ->modalDescription('Сигурни ли сте, че поръчката е потвърдена? Пратката ще се изпрати към Econt.')
                ->action(function () {
                    $this->record->updateQuietly([
                        'status' => OrderStatus::READY_FOR_SHIPMENT->value,
                    ]);

                    event(new OrderReadyForShipment($this->record->id));

                    Notification::make()
                        ->success()
                        ->title('Поръчката е потвърдена')
                        ->body('Пратката ще бъде изпратена към Econt.')
                        ->send();

                    $this->refreshUi();
                }),

            Action::make('confirm_bank_transfer')
                ->label('Потвърди банков превод')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->visible(fn () => app(ConfirmBankTransferPolicy::class)->canConfirmBankTransfer($this->record))
                ->requiresConfirmation()
                ->modalHeading('Потвърждаване на плащане')
                ->modalDescription('Сигурни ли сте, че плащането е постъпило? Пратката ще се изпрати към Econt.')
                ->action(function () {
                    $this->record->updateQuietly([
                        'payment_status' => PaymentStatus::PAID->value,
                        'status' => OrderStatus::READY_FOR_SHIPMENT->value,
                    ]);

                    event(new OrderReadyForShipment($this->record->id));

                    Notification::make()
                        ->success()
                        ->title('Плащането е потвърдено')
                        ->body('Пратката ще бъде изпратена към Econt.')
                        ->send();

                    $this->refreshUi();
                }),

            Action::make('cancel_order')
                ->label(fn () => $this->record->payment_method === 'stripe'
                    ? 'Откажи и върни плащане'
                    : 'Откажи поръчка')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => app(CancelOrderPolicy::class)->canCancelOrder($this->record))
                ->requiresConfirmation()
                ->modalHeading('Отказ на поръчка')
                ->modalDescription(fn () => $this->record->payment_method === 'stripe'
                    ? 'Ще бъде анулирана товарителницата в Econt, ще бъде върната оставащата сума през Stripe и поръчката ще бъде отказана.'
                    : 'Ще бъде анулирана товарителницата в Econt, ако има такава, и поръчката ще бъде отказана.')
                ->action(function () {
                    try {
                        app(OrderCancellationService::class)->cancel($this->record);

                        Notification::make()
                            ->success()
                            ->title('Поръчката е отказана')
                            ->body($this->record->payment_method === 'stripe'
                                ? 'Товарителницата беше анулирана и плащането беше върнато, ако е било постъпило.'
                                : 'Товарителницата беше анулирана, ако имаше такава, и клиентът беше уведомен.')
                            ->send();

                        $this->refreshUi();
                    } catch (\Throwable $e) {
                        Log::error('Order cancellation failed', [
                            'order_id' => $this->record->id,
                            'error' => $e->getMessage(),
                        ]);

                        Notification::make()
                            ->danger()
                            ->title('Неуспешен отказ на поръчка')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Action::make('request_return')
                ->label('Заяви връщане')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->visible(fn () => app(CancelOrderPolicy::class)->canRequestReturn($this->record))
                ->requiresConfirmation()
                ->modalHeading('Заявка за връщане')
                ->modalDescription(fn () => $this->record->payment_method === 'stripe'
                    ? 'Ще бъде създадена обратна пратка към Econt и оставащата сума ще бъде върната през Stripe.'
                    : 'Ще бъде създадена обратна пратка към Econt.')
                ->action(function () {
                    try {
                        app(OrderReturnRequestService::class)->requestReturn($this->record);

                        Notification::make()
                            ->success()
                            ->title('Заявено е връщане')
                            ->body($this->record->payment_method === 'stripe'
                                ? 'Създадена е обратна пратка към Econt, Stripe плащането беше обработено и клиентът беше уведомен.'
                                : 'Създадена е обратна пратка към Econt и клиентът беше уведомен.')
                            ->send();

                        $this->refreshUi();
                    } catch (\Throwable $e) {
                        Log::error('Return shipment request failed', [
                            'order_id' => $this->record->id,
                            'error' => $e->getMessage(),
                        ]);

                        Notification::make()
                            ->danger()
                            ->title('Неуспешна заявка за връщане')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Action::make('refund_stripe_payment')
                ->label('Върни плащане чрез Stripe')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('danger')
                ->visible(fn () => $this->record->payment_method === 'stripe'
                    && in_array($this->record->payment_status, [
                        PaymentStatus::PAID->value,
                        PaymentStatus::PARTIALLY_REFUNDED->value,
                    ], true)
                    && filled($this->record->stripe_payment_intent_id)
                    && (float) $this->record->refunded_amount < (float) $this->record->total
                    && ! app(CancelOrderPolicy::class)->canCancelOrder($this->record)
                    && (
                        ! app(CancelOrderPolicy::class)->canRequestReturn($this->record)
                        || $this->record->status === OrderStatus::RETURN_REQUESTED->value
                    ))
                ->schema([
                    TextInput::make('amount')
                        ->label('Сума за връщане')
                        ->prefix('EUR')
                        ->numeric()
                        ->minValue(0.01)
                        ->maxValue(fn () => max(0.01, (float) $this->record->total - (float) $this->record->refunded_amount))
                        ->default(fn () => number_format(
                            max(0, (float) $this->record->total - (float) $this->record->refunded_amount),
                            2,
                            '.',
                            '',
                        ))
                        ->required(),
                ])
                ->requiresConfirmation()
                ->modalHeading('Връщане на плащане чрез Stripe')
                ->modalDescription(fn () => $this->record->status === OrderStatus::RETURN_REQUESTED->value
                    ? 'Сумата ще бъде върната през Stripe, а поръчката ще остане със заявено връщане до доставяне на обратната пратка.'
                    : 'Сумата ще бъде върната през Stripe. При пълно връщане поръчката ще бъде маркирана като върната.')
                ->modalSubmitActionLabel('Върни сумата')
                ->action(function (array $data) {
                    try {
                        app(StripeRefundService::class)->refund(
                            $this->record,
                            (float) $data['amount'],
                            $this->record->status === OrderStatus::RETURN_REQUESTED->value
                                ? OrderStatus::RETURN_REQUESTED
                                : null,
                        );

                        Notification::make()
                            ->success()
                            ->title('Възстановяването чрез Stripe е изпратено')
                            ->body('Статусът на поръчката беше обновен.')
                            ->send();

                        $this->refreshUi();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title('Неуспешно възстановяване чрез Stripe')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }

    protected function getFormActions(): array
    {
        if ($this->record && app(IsOrderLockedPolicy::class)->isLocked($this->record)) {
            return [];
        }

        return parent::getFormActions();
    }
}
