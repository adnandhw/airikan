<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Daily Sales (Today)
        // MongoDB dates can be tricky, so we use start and end of day
        $dailySales = Transaction::where('created_at', '>=', Carbon::today()->startOfDay())
            ->where('created_at', '<=', Carbon::today()->endOfDay())
            ->sum('total_amount');

        // Monthly Sales (Current Month)
        $monthlySales = Transaction::where('created_at', '>=', Carbon::now()->startOfMonth())
            ->where('created_at', '<=', Carbon::now()->endOfMonth())
            ->sum('total_amount');

        return [
            Stat::make('Penjualan Harian', 'Rp ' . number_format($dailySales, 0, ',', '.'))
                ->description('Total penjualan hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Penjualan Bulanan', 'Rp ' . number_format($monthlySales, 0, ',', '.'))
                ->description('Total penjualan bulan ini')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
        ];
    }
}
