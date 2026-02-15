<?php

namespace App\Filament\Widgets;

use App\Models\Otp;
use Filament\Widgets\ChartWidget;

class OtpRequestsChart extends ChartWidget
{
    protected static ?string $heading = 'OTP Requests';
    protected static ?string $pollingInterval = '10s';

    public function getHeading(): string
    {
        return __('OTP Requests');
    }

    protected function getData(): array
    {
        $data = Otp::selectRaw('date(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('OTP Requests'),
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#f59e0b',
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
