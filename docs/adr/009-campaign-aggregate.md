# ADR 009: Campaign Aggregate Design and Boundaries

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 009: Campaign Aggregate Design and Boundaries
* **Status**: Approved
* **Date**: 2026-07-01

---

## Context

With the implementation of the Campaigns Module, SODARS requires a formal architecture decision record that defines the campaign aggregate root, child entity boundaries, state machine transition rules, and database schemas. The Campaigns module represents the **operational execution** layer (ad slot calendar maps, creative uploads, proof-of-performance uploads) distinct from the **commercial transaction** layer (Bookings). Decoupling these concepts allows a campaign to exist and collect creatives before the booking checkout is completed.

---

## Decision

The **Campaign** domain model is designated as the sole **Aggregate Root** for the Campaigns Module.

### 1. Boundaries and Child Ownership

All operational adjustments to a campaign's children must flow strictly through the `CampaignService` orchestrator:
* **CampaignCreative**: Represents customer-uploaded media assets for ad playback, versioned and audited.
* **CampaignSchedule**: Maps loops and slot indexes per face per day.
* **CampaignProof**: visual evidence (site photos/videos) confirming ad display playback, uploaded by providers and audited by managers.
* **CampaignNote**: Internal and public communication threads on a campaign.
* **CampaignActivity**: Business timeline audit logs with snapshots.

Direct updates to these children are prohibited.

### 2. Nullable Booking Mapping

To support the workflow where advertisers create campaigns, upload artwork, and select inventory before checking out, the `booking_id` column on the `campaigns` table is nullable. Once checkout is finalized and a commercial booking is locked, the campaign is linked to the booking. Upon booking approval, the campaign status transitions to scheduled or running.

### 3. Lifecycle State Machine

Campaigns transition strictly through the allowed paths:
* `Draft` → `Artwork Pending`
* `Artwork Pending` → `Scheduled` (once creatives are approved) or `Draft`
* `Scheduled` → `Running` or `Paused`
* `Running` → `Paused` or `Completed`
* `Paused` → `Running` or `Completed`
* `Completed` → `Archived`

### 4. CQRS Design

Decoupled CQRS repositories:
* `CampaignReadRepositoryInterface`: Queries paginated records, filters status/customer, and returns metrics.
* `CampaignWriteRepositoryInterface`: Standard mutation operations (create, update, delete, face associations).

---

## Consequences

* **Advantages**:
  * Decouples billing/checkout Commercial rules (Bookings) from creative compliance Operational rules (Campaigns).
  * Enforces immutability on completed/archived campaigns to prevent data tampering.
  * Extends clean audit tracking (`campaign_activities`) matching the Monolith design patterns.
* **Disadvantages**:
  * Requires explicit cross-module dependencies to join with Bookings later, but resolves design deadlocks.
