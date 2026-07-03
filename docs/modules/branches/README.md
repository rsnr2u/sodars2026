# Module: Branches

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this module is to define the localized business units (Branches) of SODARS, permitting decentralized operational management of regional digital screen inventories.

---

## Scope

This module covers:
* Branch profile definition and creation.
* Association of Branch Managers to their respective operational territories.
* Regional branch boundaries and override parameters.

---

## Business Rules

### 1. Operations & Status
* **Branch Entity**: Every branch must contain a unique name, code, target timezone, currency code, contact details, and list of active service cities (e.g., "Branch: North Zone", "Cities: Delhi, Noida, Gurgaon").
* **Manager Assignment**: Super Admins create Branch accounts and assign Branch Manager roles. A user can only be the active manager of one branch at a time.
* **Activity Control**: Branches have an `is_active` status. If a branch is deactivated:
  * All digital screen assets under that branch are hidden from the public marketplace map.
  * Active bookings run to completion, but new campaigns cannot be placed for its screens.

### 2. Markup Rules
* Branch Managers can define a custom markup percentage for screens managed within their branch.
* This custom markup cannot exceed the maximum markup value set in the admin settings (default 20%).

---

## Future Scope

* **Sub-branches**: Support for creating nested local sub-branches or distribution networks.
* **Autonomous Branch Billing**: Integration of independent local business banking credentials (merchant accounts) for direct local payouts.
