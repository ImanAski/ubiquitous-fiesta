<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\OtpResource;
use App\Models\Otp;
use Illuminate\Http\Resources\Json\JsonResource;

class OtpController extends Controller
{
    public function create(CreateOtpRequest $request) {
        $data = $request->validated();

        $code = Otp::generate();

        return response()->json([
            'token' => $code,
            'expires_in' => now()->addMinutes(5),
        ]);
    }
    public function verify(VerifyOtpRequest $request) {
        $data = $request->validated();

        $otp = Otp::query()->where('token', $data['token']);

        if (!$otp || !$otp->isValid($data->token)) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        $otp->delete();

        return response()->json()->status(201);
    }
}
