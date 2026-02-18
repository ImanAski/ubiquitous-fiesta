<?php

return [
    'gateways' => [
        'kishpay' => [
            'name'   => env('KISHPAY_NAME', 'Kishpay'),
            'active' => env('KISHPAY_ACTIVE', false),
        ],
        'zarinpal' => [
            'name'   => env('ZARINPAL_NAME', 'Zarinpal'),
            'active' => env('ZARINPAL_ACTIVE', false),
        ],
    ],
];
