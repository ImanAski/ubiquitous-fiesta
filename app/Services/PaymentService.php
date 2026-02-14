<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\Customers;
use App\Models\Transaction;
use App\Models\Wallet;
use Throwable;

class PaymentService
{
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
