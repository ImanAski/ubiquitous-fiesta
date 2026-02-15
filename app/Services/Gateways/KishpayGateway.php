<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayContract;
use App\Models\Currency;
use Exception;
use Http;

class KishpayGateway implements PaymentGatewayContract
{
    protected string $publicKey;
    protected string $terminalId = '09330058';
    protected string $passPhrase = '5E9596BB5D15E7F6';
    protected string $acceptorId = '992200109330058';

    public function getName(): string
    {
        return 'Kishpay';
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function generateLink(int $amount, Currency $currency, array $meta): string
    {
        $envelope = $this->getEnvelope($amount);

        $payload = [
            'authenticationEnvelope' => $envelope,
            'request' => [
                'transactionType' => 'Purchase',
                'terminalId'      => $this->terminalId,
                'acceptorId'      => $this->acceptorId,
                'Amount'          => $amount,
                'revertUri'       => route('payment.callback'), // Laravel route
                'requestId'       => (string) $meta['request_id'],
                'paymentId'       => (string) $meta['payment_id'],
                'requestTimestamp'=> time(),
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Encoding'     => 'UTF-8'
        ])->post('https://ikc.shaparak.ir/api/v3/tokenization/make', $payload);

        if ($response->failed()) {
            throw new Exception("Gateway Error: " . $response->body());
        }

        // Return the payment URL provided by the API
        return $response->json('data.paymentUrl') ?? throw new Exception("Invalid response");
    }

    private function getEnvelope(int $amount): array
    {
        $inputStr = $this->terminalId . $this->passPhrase . str_pad($amount, 2, '0', STR_PAD_LEFT) . "00";

        $aesKey = openssl_random_pseudo_bytes(16);
        $iv = openssl_random_pseudo_bytes(16);

        $plainText = pack("H*", $inputStr);
        $aesEncrypted = openssl_encrypt($plainText, 'aes-128-cbc', $aesKey, OPENSSL_RAW_DATA, $iv);

        $rsaEncrypted = '';
        $hash = hash("sha256", $rsaEncrypted, true);

        return [
            'data' => bin2hex($aesEncrypted),
            'iv' => bin2hex($iv),
        ];
    }
}
