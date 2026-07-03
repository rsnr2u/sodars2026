<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Services;

use App\Models\User;
use App\Modules\Campaigns\Domain\Entities\Campaign;
use App\Modules\Campaigns\Domain\Enums\CampaignStatus;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Modules\Inventory\Domain\Specifications\InventoryReadyForBookingSpecification;
use App\Modules\Inventory\Domain\Specifications\InventoryHasValidPricingSpecification;
use App\Modules\Inventory\Domain\Specifications\InventoryHasActiveSubscriptionSpecification;
use App\Modules\Inventory\Domain\Specifications\AvailabilityOverlapSpecification;
use App\Modules\Providers\Domain\Enums\ProviderStatus;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BookingAggregateValidator
{
    /**
     * Perform the comprehensive 10-step booking validations.
     *
     * @param array<int, array{inventory_face_id: string, start_date: string, end_date: string}> $items
     */
    public function validate(
        User $customer,
        string $branchId,
        ?string $campaignId,
        array $items
    ): void {
        // ─── 1. Campaign Eligibility ────────────────────────────────
        if ($campaignId) {
            $campaign = Campaign::find($campaignId);
            if (!$campaign) {
                $this->fail('campaign_id', 'Campaign does not exist.');
            }

            if ($campaign->customer_id !== $customer->id) {
                $this->fail('campaign_id', 'Campaign does not belong to the requesting customer.');
            }

            if ($campaign->status !== CampaignStatus::Draft && $campaign->status !== CampaignStatus::ArtworkPending) {
                $this->fail('campaign_id', 'Campaign status is not eligible for booking checkout.');
            }
        }

        // ─── 2. Permissions validation (Branch/Customer checks) ──────
        if (!$customer->hasRole(['customer_admin', 'customer_staff', 'super_admin', 'branch_manager'])) {
            $this->fail('customer_id', 'User is not authorized to place booking transactions.');
        }

        if (empty($items)) {
            $this->fail('items', 'Booking must contain at least one inventory face.');
        }

        foreach ($items as $index => $item) {
            $faceId = $item['inventory_face_id'];
            $start = Carbon::parse($item['start_date']);
            $end = Carbon::parse($item['end_date']);

            $face = InventoryFace::with(['inventory.provider', 'availabilities', 'pricings'])->find($faceId);
            if (!$face) {
                $this->fail("items.{$index}.inventory_face_id", "Face ID '{$faceId}' does not exist.");
            }

            $inventory = $face->inventory;
            if (!$inventory) {
                $this->fail("items.{$index}.inventory_face_id", "Inventory structure mapping missing.");
            }

            $provider = $inventory->provider;
            if (!$provider) {
                $this->fail("items.{$index}.inventory_face_id", "Provider configuration missing.");
            }

            // ─── 3. Date Validation ─────────────────────────────────
            if ($start->isPast() && !$start->isToday()) {
                $this->fail("items.{$index}.start_date", 'Booking start date cannot be in the past.');
            }

            if ($end->lessThan($start)) {
                $this->fail("items.{$index}.end_date", 'Booking end date must be after or equal to start date.');
            }

            // ─── 4. Face Active ──────────────────────────────────────
            if (!$face->is_active) {
                $this->fail("items.{$index}.inventory_face_id", "Inventory face is currently inactive.");
            }

            // ─── 5. Inventory Approved & Ready ──────────────────────
            $readySpec = new InventoryReadyForBookingSpecification();
            if (!$readySpec->isSatisfiedBy($inventory)) {
                $this->fail("items.{$index}.inventory_face_id", 'Inventory structure is not approved for bookings.');
            }

            // ─── 6. Provider Active ──────────────────────────────────
            $providerStatus = $provider->status instanceof \UnitEnum ? $provider->status->value : $provider->status;
            if ($providerStatus !== ProviderStatus::Verified->value && $providerStatus !== 'verified') {
                $this->fail("items.{$index}.inventory_face_id", 'Associated media provider is not verified.');
            }

            // ─── 7. Provider Subscription Active ────────────────────
            $subSpec = new InventoryHasActiveSubscriptionSpecification();
            if (!$subSpec->isSatisfiedBy($inventory)) {
                $this->fail("items.{$index}.inventory_face_id", 'Associated provider does not have an active platform subscription.');
            }

            // ─── 8. Pricing Exists ──────────────────────────────────
            $priceSpec = new InventoryHasValidPricingSpecification();
            if (!$priceSpec->isSatisfiedBy($face)) {
                $this->fail("items.{$index}.inventory_face_id", 'No valid pricing layout configured for this face.');
            }

            // ─── 9. Availability Overlap ─────────────────────────────
            $overlapSpec = new AvailabilityOverlapSpecification($faceId, $start, $end);
            $overlaps = $face->availabilities->filter(function ($avail) use ($overlapSpec) {
                $statusVal = $avail->availability_status instanceof \UnitEnum
                    ? $avail->availability_status->value
                    : (string) $avail->availability_status;

                return $overlapSpec->isSatisfiedBy($avail) && $statusVal !== 'operational';
            });

            if ($overlaps->isNotEmpty()) {
                $this->fail("items.{$index}.start_date", 'Selected dates overlap with an existing block or reservation.');
            }
        }
    }

    protected function fail(string $field, string $message): void
    {
        throw ValidationException::withMessages([
            $field => [$message],
        ]);
    }
}
