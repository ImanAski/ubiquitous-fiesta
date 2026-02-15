<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Http\Resources\TransactionResource;
use App\Filament\Resources\CustomersResource;

class LatestTransactions extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '5s';

    public function getTableHeading(): string
    {
        return __('Latest Transactions');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('sender.id')
                    ->label(__('From Customer')),
                Tables\Columns\TextColumn::make('receiver.id')
                    ->label(__('To Customer')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime(),
            ]);
    }
}
