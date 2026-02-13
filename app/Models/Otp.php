<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Otp extends Model
{
    public $fillable = [
        'token',
        'expires_at'
    ];

    public static function generate(): int {
        $code = rand(100000, 999999);

        self::query()->updateOrCreate(
            [
                'token' => Hash::make($code),
                'expires_at' => Carbon::now()->addMinutes(5)
            ]
        );

        return $code;
    }

    public function isValid(string $code): bool {
        return Hash::check($code, $this->token) && !Carbon::now()->isAfter($this->expires_at);
    }

}
