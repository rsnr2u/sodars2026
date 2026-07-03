<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Posting;

use App\Platform\Accounting\ChartOfAccounts\AccountingPeriod;
use App\Platform\Accounting\Journal\LedgerJournal;
use App\Platform\Accounting\Journal\LedgerEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PostingEngine
{
    public function __construct(protected DoubleEntryEngine $validator) {}

    /**
     * unified journal posting entrypoint.
     * @param array<LedgerLineData> $lines
     */
    public function post(JournalData $journalData, array $lines): LedgerJournal
    {
        return DB::transaction(function () use ($journalData, $lines) {
            // Find active period matching current month/year
            $month = (int) date('m');
            $year = date('Y');

            $period = AccountingPeriod::where('fiscal_year', $year)
                ->where('month', $month)
                ->first();

            if (!$period) {
                // Auto create Open period for V1 if missing
                $period = AccountingPeriod::create([
                    'id' => (string) Str::uuid(),
                    'fiscal_year' => $year,
                    'month' => $month,
                    'status' => 'open',
                ]);
            }

            // Double Entry validation checks
            $this->validator->validate($period, $lines);

            $journal = LedgerJournal::create([
                'id' => (string) Str::uuid(),
                'reference_number' => $journalData->referenceNumber,
                'narration' => $journalData->narration,
                'journal_type' => $journalData->journalType,
                'status' => 'posted',
                'reversal_of_journal_id' => null,
                'accounting_period_id' => $period->id,
                
                // Metadata
                'source_module' => $journalData->sourceModule,
                'source_id' => $journalData->sourceId,
                'source_type' => $journalData->sourceType,
                'source_event' => $journalData->sourceEvent,
                'tenant_id' => $journalData->tenantId,
                'branch_id' => $journalData->branchId,
                'posted_by' => $journalData->postedBy,
                'approved_by' => $journalData->approvedBy,

                // Trace
                'trace_id' => $journalData->traceId,
                'correlation_id' => $journalData->correlationId,
                'causation_id' => $journalData->causationId,
                'posted_at' => now(),
            ]);

            $index = 1;
            foreach ($lines as $line) {
                LedgerEntry::create([
                    'id' => (string) Str::uuid(),
                    'journal_id' => $journal->id,
                    'ledger_account_id' => $line->accountId,
                    'line_number' => $index++,
                    'entry_type' => $line->entryType,
                    'amount_cents' => $line->money->getAmount(),
                    'description' => $line->description,
                    
                    'base_currency' => $line->baseCurrency ?? $line->money->getCurrency()->getCode(),
                    'exchange_rate' => $line->exchangeRate,
                    'base_amount_cents' => (int) round($line->money->getAmount() * $line->exchangeRate),

                    'ledgerable_type' => $line->ledgerableType,
                    'ledgerable_id' => $line->ledgerableId,
                ]);
            }

            return $journal;
        });
    }

    /**
     * Create balancing reversal journal.
     */
    public function reverse(string $journalId, string $reversalRef, string $reason): LedgerJournal
    {
        return DB::transaction(function () use ($journalId, $reversalRef, $reason) {
            $originalJournal = LedgerJournal::findOrFail($journalId);

            if ($originalJournal->status === 'reversed') {
                throw new InvalidArgumentException("Journal has already been reversed.");
            }

            $originalJournal->load('entries');

            $month = (int) date('m');
            $year = date('Y');
            $period = AccountingPeriod::where('fiscal_year', $year)
                ->where('month', $month)
                ->firstOrFail();

            $reversalJournal = LedgerJournal::create([
                'id' => (string) Str::uuid(),
                'reference_number' => $reversalRef,
                'narration' => "Reversal of Journal #{$originalJournal->reference_number}. Reason: {$reason}",
                'journal_type' => 'reversal',
                'status' => 'posted',
                'reversal_of_journal_id' => $originalJournal->id,
                'accounting_period_id' => $period->id,
                'posted_at' => now(),
            ]);

            foreach ($originalJournal->entries as $entry) {
                // Invert Debit/Credit types
                $invertedType = ($entry->entry_type->value === 'debit') ? 'credit' : 'debit';

                LedgerEntry::create([
                    'id' => (string) Str::uuid(),
                    'journal_id' => $reversalJournal->id,
                    'ledger_account_id' => $entry->ledger_account_id,
                    'line_number' => $entry->line_number,
                    'entry_type' => $invertedType,
                    'amount_cents' => $entry->amount_cents,
                    'description' => "Reversing line #{$entry->line_number} of Journal #{$originalJournal->reference_number}",
                    'base_currency' => $entry->base_currency,
                    'exchange_rate' => $entry->exchange_rate,
                    'base_amount_cents' => $entry->base_amount_cents,
                ]);
            }

            $originalJournal->update(['status' => 'reversed']);

            return $reversalJournal;
        });
    }
}
