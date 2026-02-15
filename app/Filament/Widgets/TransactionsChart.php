<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class TransactionsChart extends ChartWidget
{
    protected static ?string $heading = 'Transactions Volume';
    protected static ?string $pollingInterval = '10s';

    public function getHeading(): string
    {
        return __('Transactions Volume');
    }

    protected function getData(): array
    {
        // For simplicity without installing the 'trend' package, we'll use a basic eloquent query.
        $data = Transaction::selectRaw('date(created_at) as date, sum(amount) as sum')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('Amount Transacted'),
                    'data' => $data->pluck('sum')->toArray(),
                    'fill' => 'start',
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
