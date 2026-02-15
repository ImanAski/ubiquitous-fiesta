<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Otp extends Model
{
    protected $fillable = [
        'identifier',
        'token',
        'valid',
        'expires_at'
    ];

    protected $casts = [
        'valid' => 'boolean',
        'expires_at' => 'datetime'
    ];

    public static function generate(string $identifier): int
    {
        // Invalidate previous OTPs for this identifier
        self::where('identifier', $identifier)->where('valid', true)->update(['valid' => false]);

        $code = rand(100000, 999999);

        self::create([
            'identifier' => $identifier,
            'token' => Hash::make($code),
            'valid' => true,
            'expires_at' => Carbon::now()->addMinutes(5)
        ]);

        return $code;
    }

    public function isValid(string $code): bool
    {
        return $this->valid &&
               !Carbon::now()->isAfter($this->expires_at) &&
               Hash::check($code, $this->token);
    }

}
