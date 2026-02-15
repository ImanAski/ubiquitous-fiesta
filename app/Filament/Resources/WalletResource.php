<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Filament\Resources\WalletResource\RelationManagers;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Business';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'id')
                    ->required(),
                Forms\Components\Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->required(),
                Forms\Components\TextInput::make('balance')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.id')
                    ->numeric()
                    ->sortable()
                    ->url(fn (Wallet $record): string => CustomersResource::getUrl('edit', ['record' => $record->customer_id])),
                Tables\Columns\TextColumn::make('currency.name')
                    ->numeric()
                    ->sortable()
                    ->url(fn (Wallet $record): string => CurrencyResource::getUrl('edit', ['record' => $record->currency_id])),
                Tables\Columns\TextColumn::make('balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'id')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('incrementBalance')
                        ->label('Increment Balance')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('amount')
                                ->label('Amount to add')
                                ->numeric()
                                ->required()
                                ->minValue(0),
                        ])
                        ->action(function (Wallet $record, array $data): void {
                            \Illuminate\Support\Facades\DB::transaction(function () use ($record, $data) {
                                $record->increment('balance', $data['amount']);
                                \App\Models\Transaction::create([
                                    'amount' => $data['amount'],
                                    'status' => \App\Enums\TransactionStatus::COMPLETED,
                                    'type' => \App\Enums\TransactionType::SYSTEM,
                                    'from_customer_id' => $record->customer_id,
                                    'to_customer_id' => $record->customer_id,
                                    'currency_id' => $record->currency_id,
                                    'wallet_id' => $record->id,
                                ]);
                            });
                        }),
                    Tables\Actions\Action::make('decrementBalance')
                        ->label('Decrement Balance')
                        ->icon('heroicon-o-minus-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\TextInput::make('amount')
                                ->label('Amount to subtract')
                                ->numeric()
                                ->required()
                                ->minValue(0),
                        ])
                        ->action(function (Wallet $record, array $data): void {
                            \Illuminate\Support\Facades\DB::transaction(function () use ($record, $data) {
                                $record->decrement('balance', $data['amount']);
                                \App\Models\Transaction::create([
                                    'amount' => $data['amount'],
                                    'status' => \App\Enums\TransactionStatus::COMPLETED,
                                    'type' => \App\Enums\TransactionType::SYSTEM,
                                    'from_customer_id' => $record->customer_id,
                                    'to_customer_id' => $record->customer_id,
                                    'currency_id' => $record->currency_id,
                                    'wallet_id' => $record->id,
                                ]);
                            });
                        }),
                ]),
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
            'index' => Pages\ListWallets::route('/'),
            'create' => Pages\CreateWallet::route('/create'),
            'edit' => Pages\EditWallet::route('/{record}/edit'),
        ];
    }
}
