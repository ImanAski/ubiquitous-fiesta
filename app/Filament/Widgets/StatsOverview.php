<?php

namespace App\Filament\Widgets;

use App\Models\Customers;
use App\Models\Transaction;
use App\Models\Wallet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '5s';

    protected function getStats(): array
    {
        return [
            Stat::make(__('Customers'), Customers::count())
                ->description(__('Total registered customers'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make(__('Wallets'), Wallet::count())
                ->description(__('Total active wallets'))
                ->descriptionIcon('heroicon-m-wallet')
                ->color('primary'),
            Stat::make(__('Transactions'), Transaction::count())
                ->description(__('Total processed transactions'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
        ];
    }
}
