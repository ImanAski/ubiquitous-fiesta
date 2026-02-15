<?php

namespace App\Filament\Resources\CustomersResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WalletsRelationManager extends RelationManager
{
    protected static string $relationship = 'wallets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->required(),
                Forms\Components\TextInput::make('balance')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('currency.name'),
                Tables\Columns\TextColumn::make('balance')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
                        ->action(function (\App\Models\Wallet $record, array $data): void {
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
                        ->action(function (\App\Models\Wallet $record, array $data): void {
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
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
