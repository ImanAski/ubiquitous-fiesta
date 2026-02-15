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

        $otps = Otp::where('valid', true)
            ->where('expires_at', '>', now())
            ->latest()
            ->get();

        $verifiedOtp = null;
        foreach ($otps as $otp) {
            if ($otp->isValid($data['otp_code'])) {
                $verifiedOtp = $otp;
                break;
            }
        }

        if (!$verifiedOtp) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        // Invalidate the token after use
        $verifiedOtp->update(['valid' => false]);

        return response()->json([
            'message' => 'OTP verified successfully',
            'identifier' => $verifiedOtp->identifier,
        ], 201);
    }
}
