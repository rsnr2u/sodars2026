# ADR 008: Inventory Bookable Face Architecture

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 008: Inventory Bookable Face Architecture
* **Status**: Approved
* **Date**: 2026-06-30

---

## Context

Traditional outdoor advertising platforms model inventory as a single bookable unit. However, a single physical structure (e.g., a billboard, a digital totem, a bus shelter) may expose multiple bookable surfaces facing different directions, with independent pricing, availability, and booking schedules. SODARS requires a model that separates the physical asset from its bookable surfaces to support complex booking scenarios without future schema changes.

---

## Decision

### 1. Physical Asset vs. Bookable Unit

The `Inventory` model represents the **physical asset**: its location, provider ownership, category, capabilities, compliance documents, and geographic indexing.

The `InventoryFace` model represents the **bookable unit**: the specific surface that a campaign or booking references. Each face has:
* A unique `face_code` (e.g., `INV-STA-000001-F1`)
* A human-readable `display_name` (e.g., "Front Face", "Rear Face", "Left Panel")
* A `facing_direction` enum (`north`, `south`, `east`, `west`, `northeast`, `northwest`, `southeast`, `southwest`, `omnidirectional`)
* Independent `physical_specifications` JSON (width, height, orientation, illumination)
* An `is_active` flag for operational control
* A `display_order` for UI sorting

### 2. Booking Reference Chain

When the Bookings module is implemented, the reference chain will be:

```
Booking
    └── belongsTo InventoryFace
            └── belongsTo Inventory
                    └── belongsTo Provider
```

This ensures:
* A booking always references a specific surface, not an ambiguous "inventory item."
* A provider can have one billboard with two independently priced and booked faces.
* Analytics can report per-face performance metrics.

### 3. Pricing Per Face

Each `InventoryFace` has its own pricing rates via the `InventoryPricing` model:
* Multiple pricing tiers per face (baseline, seasonal, premium, promotional, programmatic, negotiated).
* Each tier has an `effective_from` / `effective_to` date range and a `priority` for conflict resolution.
* The `InventoryPricingResolver` resolves the active rate for a given face, date range, and currency using: `resolve(InventoryFace $face, DateRange $period, Currency $currency): Money`

### 4. Availability Per Face

Each `InventoryFace` has its own availability ledger via the `InventoryAvailability` model:
* Time windows classified as `operational`, `maintenance`, `manual_block`, or `reserved`.
* The `AvailabilityOverlapSpecification` enforces that no two windows overlap for the same face.
* The `FaceAvailabilityValidator` validates booking eligibility by checking: face active status, inventory approval status, pricing existence, provider subscription validity, and availability window freedom.

### 5. Default Face Auto-Creation

When an inventory is created via the `CreateInventoryPipeline`, if no faces are explicitly provided, a default face (`F1`, "Default Face", facing `north`) is automatically created. This ensures every inventory always has at least one bookable unit.

---

## Consequences

* **Advantages**:
  * Eliminates ambiguity in booking references — every booking targets a specific physical surface.
  * Supports asymmetric pricing (e.g., highway-facing side costs more than the rear).
  * Enables per-face availability blocking without affecting other surfaces on the same structure.
  * Future digital totems with 4+ screens are trivially modeled without schema changes.
  * Marketplace search can filter by face-specific attributes (facing direction, size, illumination).
* **Disadvantages**:
  * More granular data model increases query complexity for simple single-face inventories, but the default face auto-creation mitigates this.
  * Pricing and availability queries must always scope to `inventory_face_id`, which is enforced by the repository interfaces.
