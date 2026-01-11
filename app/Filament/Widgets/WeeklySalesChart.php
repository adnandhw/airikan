<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class WeeklySalesChart extends ChartWidget
{
    protected ?string $heading = 'Penjualan Mingguan (7 Hari Terakhir)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Loop for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            
            // MongoDB-safe specific day query
            $dailySum = Transaction::where('created_at', '>=', $date->copy()->startOfDay())
                ->where('created_at', '<=', $date->copy()->endOfDay())
                ->sum('total_amount');

            $data[] = $dailySum;
            $labels[] = $date->format('d M');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan',
                    'data' => $data,
                    'borderColor' => '#10b981', // Emerald 500
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
