<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Enums;

enum InvoiceType: string
{
    case TaxInvoice = 'tax_invoice';
    case ProformaInvoice = 'proforma_invoice';
    case CreditNote = 'credit_note';
    case DebitNote = 'debit_note';
}
