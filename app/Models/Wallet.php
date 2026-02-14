<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $fillable =
        [
            "status",
            "type",
            "balance",
            "customer_id"
        ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customers::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
