<?php

namespace App\Services;

use App\Contracts\PaymentGatewayContract;
use Exception;
use Illuminate\Support\Collection;

class GatewayManager
{

    /**
     * @return Collection
     */
    public function getActiveGateways(): Collection
    {
        return collect(config('payment.gateways'))
            ->filter(fn($config) => $config['active'] == true)
            ->map(fn($config, $key) => [
                'id' => $key,
                'name' => $config['name'],
            ])
            ->values();
    }

    /**
     * @param string $id
     * @return PaymentGatewayContract
     * @throws Exception
     */
    public function find(string $id): PaymentGatewayContract
    {
        $config = config('payment.gateways.' . $id);

        if (!$config || !$config['active'] ?? false) {
            throw new Exception("Gateway [{$id}] not found");
        }

        $gatewayClass = $config['class'];

        return new $gatewayClass($id);
    }

    /**
     * @throws Exception
     */
    public function default(): PaymentGatewayContract
    {
        return $this->find(config('payment.gateways.default'));
    }

}
