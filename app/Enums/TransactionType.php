<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TransactionType: int implements HasLabel
{
    case CREDIT = 1;
    case DEBIT = 2;
    case SYSTEM = 3;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CREDIT => 'Credit',
            self::DEBIT => 'Debit',
            self::SYSTEM => 'System',
        };
    }
}
