<?php

namespace App\Http\Controllers;

use App\Services\GatewayManager;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    protected $gatewayManager;

    public function __construct(GatewayManager $gatewayManager) {
        $this->gatewayManager = $gatewayManager;
    }

    public function index()
    {
        return response()->json([
            'data' => $this->gatewayManager->getActiveGateways()
        ]);
    }
}
