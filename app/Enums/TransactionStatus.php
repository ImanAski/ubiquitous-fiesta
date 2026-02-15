<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TransactionStatus: int implements HasLabel
{
    case PENDING = 0;
    case COMPLETED = 1;
    case FAILED = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
        };
    }
}
