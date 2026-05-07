<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersByProviderChart extends ChartWidget
{
    protected ?string $heading = 'Order per Provider';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $okeconnect = Order::where('provider', 'okeconnect')->count();
        $smmpanel = Order::where('provider', 'smmpanel')->count();

        return [
            'datasets' => [
                [
                    'data' => [$okeconnect, $smmpanel],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                    ],
                ],
            ],
            'labels' => ['OkeConnect', 'SMM Panel'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
