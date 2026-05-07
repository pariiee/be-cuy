<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Hari ini
        $todayOrders = Order::today()->count();
        $todayRevenue = Order::today()->sum('sell_price');
        $todayProfit = Order::today()->sum('profit');

        // Bulan ini
        $monthOrders = Order::thisMonth()->count();
        $monthRevenue = Order::thisMonth()->sum('sell_price');
        $monthProfit = Order::thisMonth()->sum('profit');

        // Total semua
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('sell_price');
        $totalProfit = Order::sum('profit');

        return [
            Stat::make('Order Hari Ini', $todayOrders)
                ->description('Rp' . number_format($todayRevenue, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('Profit Hari Ini', 'Rp' . number_format($todayProfit, 0, ',', '.'))
                ->description('dari ' . $todayOrders . ' order')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pemasukan Bulan Ini', 'Rp' . number_format($monthRevenue, 0, ',', '.'))
                ->description('Profit: Rp' . number_format($monthProfit, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            Stat::make('Total Order', $totalOrders)
                ->description('Revenue: Rp' . number_format($totalRevenue, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
        ];
    }
}
