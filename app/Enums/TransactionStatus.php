<?php

namespace App\Enums;

enum TransactionStatus: int
{
    case PENDING = 0;
    case COMPLETED = 1;
    case FAILED = 2;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
        };
    }
}
