# ADR 011: Booking Aggregate Design and Transaction Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 011: Booking Aggregate Design and Transaction Rules
* **Status**: Approved
* **Date**: 2026-07-01

---

## Context

The Bookings module serves as the primary **transaction engine** of the SODARS ERP platform. It manages inventory reservations, payments, and workflow approvals. To ensure long-term architectural stability, several guidelines were established to keep bookings clean and audit-ready.

---

## Decision

The **Booking** entity is designated as the sole **Aggregate Root** of the Bookings module.

### 1. Booking Face Boundaries
To preserve clear structural boundaries, `booking_items` does not store the high-level `inventory_id`. Instead, it references only the specific bookable unit `inventory_face_id`. High-level inventory mapping is inferred strictly through relationship joins.

### 2. Immutable Pricing Snapshot
When checkouts occur, an immutable pricing snapshot is stored as JSON in `booking_items.pricing_snapshot`. This preserves net rates, markups, GST, platform fee splits, and provider shares permanently to guarantee invoice integrity, even if parent inventory prices are modified in the future.

### 3. Polymorphic Payment Ledger
To prevent modular coupling, manual payment records are hosted in a global `payments` table with polymorphic relation columns `paymentable_id` and `paymentable_type`. This allows wallets, subscriptions, and invoice entities to reuse the same database table.

### 4. Consolidated Precondition Validator
We introduce `BookingAggregateValidator` which validates 10 explicit criteria:
1. Campaign eligible
2. Inventory approved
3. Provider active
4. Subscription active
5. Face active
6. Availability overlap
7. Pricing exists
8. Date validation
9. Branch permissions
10. Customer permissions

### 5. Transition Lifecycles
State transitions flow exclusively through `BookingLifecycleService`, enforcing state machine boundaries and resolving ledger locking/releases automatically.

---

## Consequences

* **Advantages**:
  * Guarantees 100% immutable invoice history.
  * Allows cross-module payment ledger reuse.
  * Simplifies bulk checking rules via a consolidated validator.
* **Disadvantages**:
  * High JSON snapshot serialization overhead, but completely justified for strict financial auditability.
