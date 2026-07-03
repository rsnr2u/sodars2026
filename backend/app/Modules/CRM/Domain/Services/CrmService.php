<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Services;

use App\Modules\CRM\Domain\Entities\Account;
use App\Modules\CRM\Domain\Entities\Contact;
use App\Modules\CRM\Domain\Entities\Lead;
use App\Modules\CRM\Domain\Entities\Opportunity;
use App\Modules\CRM\Domain\Entities\Quotation;
use App\Modules\CRM\Domain\Entities\QuotationVersion;
use App\Modules\CRM\Domain\Entities\QuotationItem;
use App\Modules\CRM\Domain\Entities\FollowUp;
use App\Modules\CRM\Domain\Entities\CrmActivity;
use App\Modules\CRM\Domain\Enums\LeadStatus;
use App\Modules\CRM\Domain\Enums\QuotationStatus;
use App\Modules\CRM\Application\Pipelines\ConvertQuotationPipeline;
use App\Modules\Bookings\Domain\Entities\Booking;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CrmService
{
    public function __construct(
        protected LeadScoreCalculator $scoreCalculator,
        protected ConvertQuotationPipeline $convertPipeline
    ) {}

    /**
     * Create prospective lead and auto score it.
     */
    public function createLead(array $data): Lead
    {
        return DB::transaction(function () use ($data) {
            $lead = Lead::create([
                'id' => (string) Str::uuid(),
                'account_id' => $data['account_id'] ?? null,
                'contact_id' => $data['contact_id'] ?? null,
                'title' => $data['title'],
                'source' => $data['source'] ?? 'website',
                'status' => LeadStatus::NEW->value,
                'assigned_to' => $data['assigned_to'] ?? null,
            ]);

            // Score calculation
            $score = $this->scoreCalculator->calculate($lead);
            $lead->update(['lead_score' => $score]);

            // Audit trace
            $lead->activities()->create([
                'id' => (string) Str::uuid(),
                'performed_by' => auth()->id(),
                'activity_type' => 'created',
                'description' => "Lead created with qualification score of {$score}/100",
            ]);

            return $lead;
        });
    }

    /**
     * Qualify Lead: promotes lead and creates permanent accounts/contacts details.
     */
    public function qualifyLead(string $leadId): Lead
    {
        return DB::transaction(function () use ($leadId) {
            $lead = Lead::findOrFail($leadId);
            $lead->update(['status' => LeadStatus::QUALIFIED->value]);

            // Capture account/contact
            if (!$lead->account_id) {
                $account = Account::create([
                    'id' => (string) Str::uuid(),
                    'name' => "Company of " . $lead->title,
                ]);

                $contact = Contact::create([
                    'id' => (string) Str::uuid(),
                    'account_id' => $account->id,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'contact@sodars.com',
                    'phone' => '00000000',
                ]);

                $lead->update([
                    'account_id' => $account->id,
                    'contact_id' => $contact->id,
                ]);
            }

            $lead->activities()->create([
                'id' => (string) Str::uuid(),
                'performed_by' => auth()->id(),
                'activity_type' => 'status_change',
                'description' => 'Lead qualified and converted to account customer details.',
            ]);

            return $lead;
        });
    }

    /**
     * Create sales opportunity deal.
     */
    public function createOpportunity(array $data): Opportunity
    {
        return DB::transaction(function () use ($data) {
            $opp = Opportunity::create([
                'id' => (string) Str::uuid(),
                'account_id' => $data['account_id'],
                'contact_id' => $data['contact_id'] ?? null,
                'title' => $data['title'],
                'estimated_value_cents' => $data['estimated_value_cents'],
                'probability' => $data['probability'] ?? 10,
                'pipeline_stage_id' => $data['pipeline_stage_id'],
                'close_date' => $data['close_date'],
                'assigned_to' => $data['assigned_to'] ?? null,
            ]);

            $opp->activities()->create([
                'id' => (string) Str::uuid(),
                'performed_by' => auth()->id(),
                'activity_type' => 'created',
                'description' => "Opportunity created with initial probability of {$opp->probability}%",
            ]);

            return $opp;
        });
    }

    /**
     * Create quotation, version details, and item lines.
     */
    public function createQuotation(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            $quote = Quotation::create([
                'id' => (string) Str::uuid(),
                'opportunity_id' => $data['opportunity_id'] ?? null,
                'account_id' => $data['account_id'],
                'quotation_number' => $data['quotation_number'],
                'status' => QuotationStatus::DRAFT->value,
                'active_version_number' => 1,
            ]);

            $version = QuotationVersion::create([
                'id' => (string) Str::uuid(),
                'quotation_id' => $quote->id,
                'version_number' => 1,
                'valid_until' => $data['valid_until'],
                'subtotal_cents' => $data['subtotal_cents'],
                'discount_cents' => $data['discount_cents'] ?? 0,
                'tax_cents' => $data['tax_cents'] ?? 0,
                'grand_total_cents' => $data['grand_total_cents'],
                'currency' => $data['currency'] ?? 'INR',
                'is_active' => true,
            ]);

            foreach ($data['items'] as $item) {
                QuotationItem::create([
                    'id' => (string) Str::uuid(),
                    'quotation_version_id' => $version->id,
                    'inventory_face_id' => $item['inventory_face_id'],
                    'start_date' => $item['start_date'],
                    'end_date' => $item['end_date'],
                    'daily_frequency' => $item['daily_frequency'] ?? 1,
                    'price_cents' => $item['price_cents'],
                ]);
            }

            $quote->activities()->create([
                'id' => (string) Str::uuid(),
                'performed_by' => auth()->id(),
                'activity_type' => 'created',
                'description' => "Quotation proposal drafted with version 1",
            ]);

            return $quote;
        });
    }

    /**
     * Convert quote proposal to active Booking checkout.
     */
    public function convertQuotation(string $quotationId, string $branchId, string $customerId): Booking
    {
        $quote = Quotation::with(['activeVersion.items'])->findOrFail($quotationId);
        return $this->convertPipeline->execute($quote, $branchId, $customerId);
    }
}
