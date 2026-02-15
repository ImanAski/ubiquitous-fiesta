<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChargeRequest;
use App\Http\Requests\PayRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\Gateways\KishpayGateway;
use App\Services\PaymentService;

class TransactionController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected KishpayGateway $kishpayGateway
    ) {}

    public function index()
    {
        return TransactionResource::collection(Transaction::all());
    }

    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction);
    }

    public function charge(ChargeRequest $request)
    {
        $validated = $request->validated();
        $wallet = Wallet::find($validated['wallet_id']);
        $currency = $wallet->currency;

        $data = [
            'customer_id' => $wallet->customer_id,
            'wallet_id' => $validated['wallet_id'],
            'request_id' => $request->fingerprint()
        ];


        try {
            $result = $this->paymentService->generateChargeLink(
                $validated['amount'],
                $currency,
                $data
            );
            return response()->json([
                'message' => __('Success'),
                'transaction_id' => $result['transaction'],
                'link' => $result['link'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }

    }

    /**
     * @throws \Throwable
     */
    public function pay(PayRequest $request)
    {
        $validated = $request->validated();
        $wallet = Wallet::findOrFail($validated['wallet_id']);

        try {
            $transaction = $this->paymentService->payWithWallet($wallet, $validated['amount']);

            $wallet->refresh();

            return response()->json([
                'message' => __('Success'),
                'transaction_id' => $transaction->id,
                'new_balance' => $wallet->balance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function transfer(TransferRequest $request)
    {
        $validated = $request->validated();

        $fromWallet = Wallet::findOrFail($validated['from_wallet_id']);
        $toWallet = Wallet::findOrFail($validated['to_wallet_id']);

        try {
            $transaction = $this->paymentService->transfer($fromWallet, $toWallet, $validated['amount']);

            return response()->json([
                'message' => __('Success'),
                'transaction_id' => $transaction->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
