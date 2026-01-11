<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class MonthlySalesChart extends ChartWidget
{
    protected ?string $heading = 'Penjualan Bulanan (Tahun Ini)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        $currentYear = Carbon::now()->year;

        // Loop for all 12 months of the current year
        for ($month = 1; $month <= 12; $month++) {
            $date = Carbon::createFromDate($currentYear, $month, 1);
            
            // MongoDB-safe specific month query
            $monthlySum = Transaction::where('created_at', '>=', $date->copy()->startOfMonth())
                ->where('created_at', '<=', $date->copy()->endOfMonth())
                ->sum('total_amount');

            $data[] = $monthlySum;
            $labels[] = $date->format('M');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan',
                    'data' => $data,
                    'backgroundColor' => '#3b82f6', // Blue 500
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
