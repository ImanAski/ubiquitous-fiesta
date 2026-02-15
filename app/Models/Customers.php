<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;

class Customers extends Model
{
    /** @use HasFactory<\Database\Factories\CustomersFactory> */
    use HasFactory;

    protected $fillable = [
        "metadata",
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array'
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_customer_id');
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'customer_id');
    }

    public function scopeSearchMetadata(\Illuminate\Database\Eloquent\Builder $query, string $key, $value, bool $exact = true): \Illuminate\Database\Eloquent\Builder
    {
        if ($exact) {
            return $query->where("metadata->$key", $value);
        }

        return $query->where("metadata->$key", 'like', "%$value%");
    }
}
