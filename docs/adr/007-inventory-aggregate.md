# ADR 007: Inventory Aggregate Design and Boundaries

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 007: Inventory Aggregate Design and Boundaries
* **Status**: Approved
* **Date**: 2026-06-30

---

## Context

With the implementation of the Inventory Module, SODARS requires a formal architecture decision record that defines the aggregate root, child entity boundaries, state machine rules, and data contracts for inventory assets. The Inventory module becomes the canonical source of truth for every advertising asset in the platform. All downstream modules (Campaigns, Bookings, Marketplace, Pricing, and Analytics) will reference this aggregate without modifying it directly.

---

## Decision

The **Inventory** domain model is designated as the sole **Aggregate Root** for all inventory-related concepts.

### 1. Boundaries and Child Ownership

All mutations must flow strictly through the `InventoryService` orchestrator:
* **InventoryFace**: Bookable units attached to a physical structure. Each face has its own pricing, availability, and physical specifications.
* **InventoryPricing**: Versioned pricing rates (baseline, seasonal, premium, promotional, programmatic, negotiated) tied to individual faces with effective date ranges and priority ordering.
* **InventoryAvailability**: Availability ledger entries tracking operational, maintenance, manual block, and reservation windows with no overlap permitted per face.
* **InventoryDocument**: Compliance documents (Municipal Permit, Lease Agreement, Insurance, Ownership Proof, Tax Documents) linked polymorphically to the central `MediaLibrary` table.
* **InventoryMedia**: Gallery images, drone photos, street view, night photos, creative mockups, videos, and 360-degree images linked via Shared Media Library.
* **InventoryTag**: Categorization tags using a many-to-many polymorphic `inventory_taggables` junction table.
* **InventoryActivity**: Business timeline auditing of all inventory mutations with old/new value snapshots and trace metadata.

Direct controller modifications to these children are strictly prohibited.

### 2. Physical Structure vs. Bookable Face

The Inventory aggregate represents the **physical asset** (the structure, its location, provider ownership, and capabilities). The `InventoryFace` represents the **bookable unit**. This separation ensures:
* Bookings reference `InventoryFace`, not `Inventory` directly.
* A single billboard with two sides is modeled as one Inventory with two Faces.
* Pricing, availability, and booking granularity are per-face.

### 3. Creation Pipeline

Creating an inventory must execute through a transactional Pipeline (`CreateInventoryPipeline`):
`ValidateInput` → `ResolveProvider` → `ResolveBranch` → `CreateInventory` → `CreateFaces` → `CreatePricing` → `CreateAvailability` → `PublishEvents`

Geographic resolution stores direct foreign keys (`country_id`, `state_id`, `district_id`, `city_id`, `pincode_id`) alongside `branch_id` to support radius searches, analytics, reporting, and marketplace filters without expensive joins.

### 4. CQRS Design

The Repository layer is split to support clean read/write segregation:
* `InventoryReadRepositoryInterface`: Queries details, search with filter chaining, and geohash + Haversine nearby radius queries.
* `InventoryWriteRepositoryInterface`: Creates, updates, and soft deletes inventory instances.
* `InventoryFaceRepositoryInterface`, `InventoryPricingRepositoryInterface`, `InventoryAvailabilityRepositoryInterface`: Child entity CRUD.

### 5. Lifecycle State Machine

Status transitions must strictly respect the allowed paths:
* `Draft` → `Pending Approval`
* `Pending Approval` → `Approved` or `Rejected`
* `Approved` → `Suspended` or `Decommissioned`
* `Suspended` → `Approved` or `Decommissioned`
* `Rejected` → `Draft`

### 6. Geographic Indexing

Each inventory stores a precomputed `geo_hash` (base32, length 12) generated from latitude/longitude coordinates. The `InventoryReadRepository` uses geohash prefix matching for fast pre-filtering, then refines results using the Haversine formula for accurate distance calculations within radius boundaries.

### 7. Availability Overlap Rules

No two availability windows may overlap for the same `InventoryFace`. When overlaps exist, the highest-priority status determines availability:
1. Maintenance (highest)
2. Manual Block
3. Booking Reservation
4. Operational (lowest)

The `AvailabilityOverlapSpecification` enforces this invariant at the domain level.

---

## Consequences

* **Advantages**:
  * Clean aggregate boundaries prevent Campaigns, Bookings, and Marketplace modules from introducing tight coupling.
  * Face-level granularity supports complex booking scenarios (partial billboard, multi-face digital totems).
  * Precomputed geohash indexing enables sub-millisecond nearby queries at scale.
  * Versioned pricing with priority ordering supports dynamic rate management without data migrations.
* **Disadvantages**:
  * Additional boilerplate for face-level pricing and availability, but this provides maximum flexibility for the Bookings module.
  * Geographic denormalization (storing country/state/city IDs directly) requires consistency maintenance, but dramatically simplifies reporting queries.
