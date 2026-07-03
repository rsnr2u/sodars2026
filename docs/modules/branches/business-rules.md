# Branch Module: Business Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the business logic, security constraints, validation boundaries, and relational ownership rules applied to the Branch module.

---

## Scope

This document specifies the rules governing Branches, display owners (Providers), and digital screen assets (Inventory), serving as validation logic specifications for the backend developers.

---

## Business Rules

### 1. Ownership & Association Rules
* **Provider Home Branch**:
  * Every Provider is assigned to exactly **one default Branch** upon onboarding, derived from the provider's registered address city.
  * A single Branch can verify and manage multiple Providers.
* **Inventory Managing Branch**:
  * Every screen/digital asset belongs to exactly **one managing Branch**, derived from the screen's physical geolocated city.
  * A single Branch can manage multiple screen inventories.
* **Cross-Branch Provider Assets**:
  * A Provider can own screen assets managed by different Branches (e.g., if a provider company owns screens in both Delhi and Mumbai).
  * However, each individual screen asset is strictly assigned to one managing Branch. The Provider's corporate profile ownership of the screen remains unchanged when the screen is assigned to a specific Branch.

### 2. Authorization Rules
* **Head Office (Super Admin)**:
  * Has unrestricted access to view, create, edit, deactivate, and aggregate records across all branches.
* **Branch Managers**:
  * Can view, edit, and audit data only for the branch they are explicitly assigned to in the `branch_users` pivot mapping.
  * Attempts to query or modify data belonging to other branches must return an HTTP 403 Forbidden error.

### 3. Financial Markup Rules
* **Markup Ceilings**:
  * The global maximum markup limit is defined in the system settings database (default **20%**).
  * Branch Managers can define a branch-specific markup override (e.g., 15%).
  * The API must reject any branch update request where `markup_percentage` is less than 0 or greater than the global max setting.

### 4. Inventory Transfers
* **Transfer Ownership**:
  * Transferring a screen asset from Branch A to Branch B updates the screen's `branch_id` foreign key.
  * When transferred, the screen immediately inherits the pricing markup rules of Branch B.
  * Existing, paid, or active bookings prior to the transfer retain the pricing and branch attribution rules under which they were checked out. Historical financial logs must not be altered post-transfer.

---

## Future Scope

* **Franchise Profit Splits**: Logic rules for distributing portion of markups to franchise branch accounts.
