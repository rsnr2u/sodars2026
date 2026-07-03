<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Journal;

enum EntryType: string
{
    case Debit = 'debit';
    case Credit = 'credit';
}
