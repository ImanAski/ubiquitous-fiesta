<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\Customers;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\Gateways\KishpayGateway;
use Throwable;

class PaymentService
{
    public function __construct(
        protected KishpayGateway $gateway,
    ) {}

    /**
     * @param int $amount
     * @param Currency $currency
     * @param array $data
     * @return array
     * @throws Throwable
     */
    public function generateChargeLink(int $amount, Currency $currency, array $data): array
    {
        return \DB::transaction(function () use ($amount, $currency, $data) {
            $transaction = Transaction::create([
                'amount' => $amount,
                'currency_id' => $currency->id,
                'to_customer_id' => $data['customer_id'],
                'from_customer_id' => $data['customer_id'],
                'status' => TransactionStatus::PENDING,
                'type' => TransactionType::CREDIT,
                'wallet_id' => $data['wallet_id'],
            ]);

            $data['payment_id'] = $transaction->id;

            $generatedLink = $this->gateway->generateLink(
                $amount,
                $currency,
                $data
            );

            return [
                'link' => $generatedLink,
                'transaction' => $transaction,
            ];
        });
    }

    /**
     * @throws Throwable
     */
    public function transfer(Wallet $from, Wallet $to, int $amount): Transaction
    {
        return \DB::transaction(function () use ($from, $to, $amount) {
            $lockedFromWallet = Wallet::where('id', $from->id)->lockForUpdate()->first();
            $lockedToWallet = Wallet::where('id', $to->id)->lockForUpdate()->first();

            throw_if($lockedFromWallet->balance < $amount, new \Exception("Insufficient balance"));

            $lockedToWallet->increment('balance', $amount);
            $lockedFromWallet->decrement('balance', $amount);

            return Transaction::create([
                'from_customer_id' => $from->customer_id,
                'to_customer_id' => $to->customer_id,
                'amount' => $amount,
                'status' => TransactionStatus::COMPLETED,
                'wallet_id' => $lockedFromWallet->id,
                'currency_id' => $lockedFromWallet->currency_id,
                'type' => TransactionType::DEBIT,
            ]);
        });
    }

    /**
     * @param Wallet $wallet
     * @param int $amount
     * @return Transaction
     * @throws Throwable
     */
    public function payWithWallet(Wallet $wallet, int $amount): Transaction
    {
        return \DB::transaction(function () use ($wallet, $amount) {

            $lockedWallet = Wallet::where('id', $wallet->id)
                ->lockForUpdate()
                ->first();

            throw_if($lockedWallet->balance < $amount, new \Exception("Insufficient balance"));

            $lockedWallet->decrement('balance', $amount);

            return Transaction::create([
                'wallet_id' => $lockedWallet->id,
                'amount' => $amount,
                'status' => TransactionStatus::COMPLETED,
                'from_customer_id' => $lockedWallet->customer_id,
                'to_customer_id' => $lockedWallet->customer_id,
                'currency_id' => $lockedWallet->currency_id,
                'type' => TransactionType::DEBIT,
            ]);
        });
    }
    /**
     * @throws Throwable
     */
    public function executePayment(int $fromId, int $toId, int $amount)
    {
        return \DB::transaction(function () use ($fromId, $toId, $amount) {
            $sender = Wallet::find($fromId);
            $receiver = Wallet::find($toId);

            \LaravelIdea\throw_if($sender->balance < $amount, "Insufficient balance");

            $sender->increment('balance', $amount);

            $receiver->increment('balance', $amount);

            return Transaction::create([
                'from_customer_id' => $sender->customer()['id'],
                'to_customer_id' => $receiver->customer()['id'],
                'amount' => $amount,
                'status' => TransactionStatus::COMPLETED
            ]);
        });
    }
}
