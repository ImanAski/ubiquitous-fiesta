<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\OtpResource;
use App\Models\Otp;
use Illuminate\Http\Resources\Json\JsonResource;

class OtpController extends Controller
{
    public function create(CreateOtpRequest $request)
    {
        $data = $request->validated();

        $code = Otp::generate($data['identifier']);

        return response()->json([
            'token' => $code,
            'expires_in' => now()->addMinutes(5),
        ]);
    }

    public function verify(VerifyOtpRequest $request)
    {
        $data = $request->validated();

        $otp = Otp::where('identifier', $data['identifier'])
            ->where('valid', true)
            ->latest()
            ->first();

        if (!$otp || !$otp->isValid($data['token'])) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        // Invalidate the token after use
        $otp->update(['valid' => false]);

        return response()->json(['message' => 'OTP verified successfully'], 201);
    }
}
