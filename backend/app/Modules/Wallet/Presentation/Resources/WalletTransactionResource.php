<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'ledger_journal_id' => $this->ledger_journal_id,
            'amount_cents' => $this->amount_cents,
            'running_balance_snapshot' => $this->running_balance_snapshot,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'reference_number' => $this->reference_number,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
