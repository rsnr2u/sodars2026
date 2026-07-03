# ADR 006: Provider Aggregate Design and Boundaries

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 006: Provider Aggregate Design and Boundaries
* **Status**: Approved
* **Date**: 2026-06-30

---

## Context

With the implementation of the Providers Module, we need to document the explicit boundary limits, aggregate design rules, and data contracts to prevent future architectural regression. As SODARS grows to support Inventory, Marketplace, Campaigns, Bookings, Finance, and Analytics, the Provider boundaries must remain clean and isolated.

---

## Decision

The **Provider** domain model is designated as the sole **Aggregate Root** for all provider-related concepts.

### 1. Boundaries and Child Ownership
All mutations related to the provider's child records must flow strictly through the `ProviderService` orchestrator:
* **ProviderAddress**: Dedicated normalized addresses (supporting lat/long, pincodes, and primary status).
* **ProviderContact**: Multiple contacts categorized by type (`Owner`, `Accounts`, `Operations`, `Sales`, `Emergency`).
* **ProviderDocument**: Versioned compliance files linked polymorphically to the central `MediaLibrary` table using the core `HasMedia` trait.
* **ProviderStaff**: Workspace member user mappings, referencing Spatie roles for authorization without duplicating role data.
* **ProviderSubscription**: Current billing limits (max active screens, billing cycle, and starts/ends dates).
* **ProviderBankAccount**: Payout accounts with verification statuses.
* **ProviderSetting**: Config JSON cast to the typed `ProviderSettings` value object.
* **ProviderActivity**: Timeline auditing of business actions.

Direct controller modifications to these children are strictly prohibited.

### 2. Registration Pipeline
Creating a provider must execute through a transactional Pipeline (`RegisterProviderPipeline`):
`ValidateInput` → `ValidateUniqueness` → `ResolveBranch` → `CreateProvider` → `CreateAddress` → `CreateSettings` → `CreateSubscription` → `CreateAdmin` → `PublishEvents`

Branch routing flows through a geographic fallback hierarchy: Pincode → City → Coverage → District → State → Default Head Office.

### 3. CQRS Design
The Repository layer is split to support clean read/write segregation:
* `ProviderReadRepositoryInterface`: Queries details, searches, and compiles the `ProviderDashboardDTO`.
* `ProviderWriteRepositoryInterface`: Creates, updates, and soft deletes provider instances.

### 4. Lifecycle State Machine
Status transitions must strictly respect the allowed paths:
* `Draft` → `Pending`
* `Pending` → `Verified` or `Draft` (on document rejection)
* `Verified` → `Suspended` or `Deactivated`
* `Suspended` → `Verified` or `Deactivated`

---

## Consequences

* **Advantages**:
  * Clean boundary isolation prevents other business modules (e.g. Inventory, Bookings) from introducing tight coupling.
  * Explicit state machine transitions guarantee replay safety and webhook stability.
  * Return of pure DTOs (e.g. `ProviderDashboardDTO`) keeps the Presentation layer decoupled from database structures.
* **Disadvantages**:
  * Requires additional boilerplate DTO and Specification classes, but provides maximum typing safety.
