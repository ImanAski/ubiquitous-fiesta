<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Filament\Resources\CustomersResource;
use App\Filament\Resources\WalletResource;
use App\Filament\Resources\CurrencyResource;
use App\Models\Transaction;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('Business');
    }

    public static function getModelLabel(): string
    {
        return __('Transaction');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Transactions');
    }

    public static function getNavigationLabel(): string
    {
        return __('Transactions');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Select::make('status')
                    ->options(\App\Enums\TransactionStatus::class)
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options(\App\Enums\TransactionType::class)
                    ->required(),
                Forms\Components\Select::make('from_customer_id')
                    ->relationship('sender', 'id')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('to_customer_id')
                    ->relationship('receiver', 'id')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->required(),
                Forms\Components\Select::make('wallet_id')
                    ->relationship('wallet', 'id')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sender.id')
                    ->label('From Customer')
                    ->sortable()
                    ->url(fn (Transaction $record): string => CustomersResource::getUrl('edit', ['record' => $record->from_customer_id])),
                Tables\Columns\TextColumn::make('receiver.id')
                    ->label('To Customer')
                    ->sortable()
                    ->url(fn (Transaction $record): string => CustomersResource::getUrl('edit', ['record' => $record->to_customer_id])),
                Tables\Columns\TextColumn::make('currency.name')
                    ->sortable()
                    ->url(fn (Transaction $record): string => CurrencyResource::getUrl('edit', ['record' => $record->currency_id])),
                Tables\Columns\TextColumn::make('wallet.id')
                    ->label('Wallet')
                    ->sortable()
                    ->url(fn (Transaction $record): string => WalletResource::getUrl('edit', ['record' => $record->wallet_id])),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sender')
                    ->relationship('sender', 'id')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('receiver')
                    ->relationship('receiver', 'id')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->options(\App\Enums\TransactionType::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
