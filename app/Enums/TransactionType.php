<?php

namespace App\Enums;

enum TransactionType: int
{
    case CREDIT = 1;
    case DEBIT = 2;
}
