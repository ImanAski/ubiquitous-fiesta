<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'amount',
        'status',
        'type',
        'from_customer_id',
        'to_customer_id',
        'currency_id',
        'wallet_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransactionStatus::class,
            'type' => TransactionType::class,
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Customers::class, 'from_customer_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Customers::class, 'to_customer_id');
    }


    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
