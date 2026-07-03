# ADR 015: CRM and Commercial Sales Pipeline

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 015: CRM and Commercial Sales Pipeline
* **Status**: Approved
* **Date**: 2026-07-01

---

## Context

SODARS needs a formal customer-acquisition and sales quotation pipeline to capture client enquiries, qualify leads, log follow-up actions, and issue itemized quotations before bookings checkout. All quotation details (pricing, flight durations, faces) must translate directly into bookings without manual re-entry.

---

## Decision

We establish the following CRM boundaries and rules:

### 1. Bounded Decoupling of Snapshots
Quotations utilize versions (`crm_quotation_versions` and `crm_quotation_items`). When an active quotation version is converted to a booking, the active quotation proposal details are frozen as an immutable `quotation_snapshot` on the booking, keeping it distinct from any subsequent booking-level modifications (like post-approval adjustments) which remain in `booking_snapshot`.

### 2. Opportunity Bounded Aggregate Root
Opportunities (`crm_opportunities`) are modelled as an independent aggregate root separate from leads. Opportunities store estimated value, probability (e.g. 70%), and weighted expected value to support forecast calculations.

### 3. ConvertQuotationPipeline
We isolate the booking checkout integration using `ConvertQuotationPipeline`. It locks active version records and handles booking checkouts through standard API interfaces:
1. **Validate Quote**: Confirms status is `accepted` and valid.
2. **Validate Availability**: Submits availability block checks for target inventory faces.
3. **Freeze Snapshot**: Serializes the quotation version details into the booking's `quotation_snapshot` field.
4. **Checkout**: Creates the final `Booking` record.

### 4. Pluggable Lead Scoring
We deploy a `LeadScoreStrategy` contract. The default V1 `RuleBasedLeadScore` assigns points (0-100) based on source channel, budget size, and timeline details.

---

## Consequences

* **Advantages**:
  * Unifies lead acquisition and billing checkouts cleanly.
  * Ensures complete audit trails of quotation proposals independent of booking allocations.
  * Extensible AI hooks via `LeadScoreStrategy`.
* **Disadvantages**:
  * Couples the CRM module with Bookings during conversion, which is resolved via standard service interfaces.
