<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Contracts;

use App\Core\ValueObjects\Money;

interface FinancialDocument
{
    public function documentNumber(): string;
    public function organizationId(): ?string;
    public function totalAmount(): Money;
    public function currency(): string;
    public function postingReference(): string;
}
