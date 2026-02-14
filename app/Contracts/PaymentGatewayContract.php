<?php

namespace App\Contracts;

use App\Models\Currency;

interface PaymentGatewayContract
{
    /**
     * @param int $amount
     * @param Currency $currency
     * @param array $metadata
     * @return string
     */
    public function generateLink(int $amount, Currency $currency, array $metadata): string;
}
