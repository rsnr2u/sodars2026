# Provider Module: Business Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the business logic, security constraints, validation boundaries, and relational boundaries governing Providers and their subscription lists.

---

## Scope

This document specifies the rules applied to Provider accounts, profile verifications, banking updates, billing tiers, and data boundaries.

---

## Business Rules

### 1. Identity & Profile Rules
* **One Provider = One Company**: A Provider profile is mapped to exactly one registered legal company or entity (identified by a unique business tax registration number). Multiple separate provider companies cannot merge under a single account.
* **Geographical Assets**: A single Provider can own digital screen inventory spread across multiple geographical districts or branch zones.
* **Branch Management Routing**:
  * Every Provider has exactly **one default Branch** assigned upon registration (based on company city).
  * However, individual screens owned by the Provider are governed by their respective local Managing Branches. Thus, different screens can be overseen by different Branch Managers.
  * Reassigning a screen to a new Managing Branch does not affect the Provider's ownership of the asset.

---

### 2. Marketplace & Net Pricing Rules
* **Optional Marketplace Listings**:
  * Marketplace participation is optional.
  * Providers can toggle `enable_marketplace` to 0 to temporarily hide all listings.
* **Net Price Guarantee**:
  * Providers set the **Net Price** for their screens.
  * The Retail Selling Price on the marketplace is calculated automatically by applying the branch-specific markup configuration:
    $$\text{Retail Price} = \text{Net Price} \times (1 + \text{Branch Markup})$$
  * The Provider always receives the exact Net Price for completed bookings. SODARS retains the markup difference.

---

### 3. Data Isolation & Security Boundaries
* **Provider Isolation**:
  * A Provider can only view and access their own account details, staff, screen inventory lists, schedules, and financial analytics.
  * A Provider cannot query or view listings, calendars, or earnings of other Providers.
* **Staff Access Control**:
  * Only users mapped in the `provider_staff` table as `provider_admin` can edit bank details, invite staff, or change subscription levels.
  * User accounts with the `provider_staff` role can view calendars and download customer ad creative artwork files, but cannot modify banking details.
* **Admin Visibility Grid**:
  * Branch Managers only see Providers whose default branch matches their assigned `branch_id`.
  * Head Office (Super Admin) has global visibility and can view all providers and their bank account ledgers.

---

## Future Scope

* **Provider SLA Enforcement**: Penalties applied to payout balances if provider screen uptime drops below 95%.
