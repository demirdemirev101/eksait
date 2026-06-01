<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '300s';

    protected function getStats(): array
    {
        $todayRevenue = $this->revenueQuery()
            ->whereBetween('created_at', [today()->startOfDay(), today()->endOfDay()])
            ->sum('total');

        $monthRevenue = $this->revenueQuery()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        $averageMonthlyRevenue = $this->averageMonthlyRevenue();
        $pendingOrders = Order::query()
            ->whereIn('status', [
                OrderStatus::PENDING->value,
                OrderStatus::PENDING_REVIEW->value,
                OrderStatus::PROCESSING->value,
            ])
            ->count();

        $cancelled = $this->statusSummary(OrderStatus::CANCELLED->value);
        $returned = $this->statusSummary(OrderStatus::RETURNED->value);

        return [
            Stat::make('Оборот днес', $this->money($todayRevenue))
                ->description('Поръчки без отказани и върнати')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Оборот този месец', $this->money($monthRevenue))
                ->description(now()->translatedFormat('F Y'))
                ->icon('heroicon-o-chart-bar-square')
                ->color('success'),
            Stat::make('Средно месечно', $this->money($averageMonthlyRevenue))
                ->description('Среден оборот за активните месеци')
                ->icon('heroicon-o-calculator')
                ->color('info'),
            Stat::make('Чакащи поръчки', $pendingOrders . ' бр.')
                ->description('В очакване, за преглед или обработка')
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Отказани', $cancelled['count'] . ' бр.')
                ->description($this->money($cancelled['total']))
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
            Stat::make('Върнати', $returned['count'] . ' бр.')
                ->description($this->money($returned['total']))
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray'),
        ];
    }

    private function revenueQuery(): Builder
    {
        return Order::query()
            ->whereNotIn('status', [
                OrderStatus::CANCELLED->value,
                OrderStatus::RETURN_REQUESTED->value,
                OrderStatus::RETURNED->value,
            ])
            ->whereNotIn('payment_status', ['failed', 'partially_refunded', 'refunded']);
    }

    private function averageMonthlyRevenue(): float
    {
        $firstOrderDate = $this->revenueQuery()->min('created_at');

        if (! $firstOrderDate) {
            return 0.0;
        }

        $months = max(1, Carbon::parse($firstOrderDate)->startOfMonth()->diffInMonths(now()->startOfMonth()) + 1);

        return (float) $this->revenueQuery()->sum('total') / $months;
    }

    /**
     * @return array{count: int, total: float}
     */
    private function statusSummary(string $status): array
    {
        $query = Order::query()->where('status', $status);

        return [
            'count' => (int) $query->count(),
            'total' => (float) $query->sum('total'),
        ];
    }

    private function money(float | int | string | null $amount): string
    {
        return number_format((float) $amount, 2, '.', ' ') . ' EUR';
    }
}
