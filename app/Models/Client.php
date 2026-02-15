<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    protected $fillable =
        [
            "name",
            "token"
        ];

    protected static function booted()
    {
        static::creating(function ($client) {
            $client->token = (string) \Illuminate\Support\Str::ulid();
        });
    }
}
