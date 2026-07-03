# ADR 013: Financial Event Chain and Accounting Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 013: Financial Event Chain and Accounting Rules
* **Status**: Approved
* **Date**: 2026-07-01

---

## Context

The billing, tax calculation, provider payout splits, and revenue recognition rules must adhere to strict accounting standards (GAAP/IFRS). Scattering these rules across controllers or database models leads to rounding bugs, incorrect tax filings, and audibility failure. We need to formalize the financial state flow and event triggers.

---

## Decision

We establish the following financial flow and accounting specifications:

### 1. Bounded Context Structure
The Finance module (`App\Modules\Finance`) is separated into three sub-contexts:
* `Invoicing`: Manages client billing aggregates (Invoices, Items, Taxes, Adjustments).
* `Settlement`: Manages partner splits aggregates (ProviderSettlements, Items, Payout Adjustments).
* `RevenueRecognition`: Manages earnings schedules and entries.

### 2. Financial Event Chain
Workflows progress through sequential events:

```
Booking Approved (State: approved)
        │
        ▼
Event: booking.status_changed.v1 (Target: approved)
Action: Generate Proforma Invoice (Type: proforma_invoice, Status: draft)
        │
        ▼
Payment Recorded & Verified (State: verified)
        │
        ▼
Event: booking.payment_audited.v1 (Target: verified)
Action: Generate Tax Invoice (Type: tax_invoice, Status: issued)
        │
        ▼
Event: invoice.issued.v1
Action: Initialize Revenue Recognition Schedule (Status: pending)
Action: Create Provider Settlement Aggregate (Status: pending)
        │
        ▼
Daily Cron Execution / Manual Trigger
Action: Execute Revenue Recognition Entries (Recognize daily split)
Action: Process Provider Settlements (Shifts to paid upon payout verification)
```

### 3. Absolute Immutability: Booking Snapshot
To guarantee rendering integrity, invoices store a complete `booking_snapshot` JSON payload capturing:
* Client company details & address (for historical billing)
* Branch settings & GST registration codes
* Provider company details & payout settings
* Booking items details, daily frequencies, and flight dates
* Mapped baseline prices and markups

### 4. Bounded Calculations
* **GST Taxation**: CGST (9%) and SGST (9%) are applied if the customer and branch reside in the same state (intra-state transaction). IGST (18%) is applied if they reside in different states (inter-state transaction).
* **Linear Earning Recognition**: Daily recognition split divides the booking item retail cost linearly by total flight days. On each flight day, a job executes to move the amount from deferred to recognized status in `revenue_recognition_entries`.

---

## Consequences

* **Advantages**:
  * Compliant with standard GAAP/IFRS deferred revenue rules.
  * Audit-trail immutability through JSON booking snapshots.
  * Highly decoupling through event-driven triggers.
* **Disadvantages**:
  * Demands strict setup (e.g. state mappings, address formats) during initial checkout, which is fully covered by our test harness.
