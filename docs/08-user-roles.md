# 08. User Roles

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to specify the system user roles, access control boundaries, and authorization permissions within SODARS.

---

## Scope

This document details:
* Definitions of the five core roles.
* Permission matrix mapping roles to resources.
* Token-based role verification rules.

---

## Business Rules

### 1. Role Definitions

* **Super Admin (Head Office)**:
  * Has global, unrestricted access to the entire platform.
  * Can configure branches, override default markup limits, view global revenues, and manage global settings.
* **Branch Manager**:
  * Oversees a single branch (assigned by Super Admin).
  * Approves or rejects screen inventory submissions in their region.
  * Can customize markups for their branch assets (up to the global maximum limit).
  * Manages regional providers and customers.
* **Provider (Asset Owner)**:
  * Registers and lists outdoor digital screens.
  * Sets the Net Price for their screens.
  * Views booking schedules and downloads customer-uploaded ad creatives for execution.
* **Customer (Advertiser)**:
  * Self-registers on the platform.
  * Searches the marketplace, builds campaigns, places orders, and uploads ad creatives.
* **Guest (Unauthenticated)**:
  * Public visitor to the marketplace website.
  * Can browse screens on Google Maps, but cannot view checkout features, place orders, or upload content without registering.

### 2. Authorization Permission Matrix

| Feature / Resource | Guest | Customer | Provider | Branch Manager | Super Admin |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Browse Marketplace & Map** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Add to Cart & Configure Campaign** | ✅ | ✅ | ❌ | ❌ | ✅ |
| **Complete Booking & Pay** | ❌ | ✅ | ❌ | ❌ | ✅ |
| **Upload Ad Creative / Content** | ❌ | ✅ (Own) | ❌ | ❌ | ✅ |
| **Submit New Screen / Asset** | ❌ | ❌ | ✅ | ❌ | ✅ |
| **Approve Screen Inventory** | ❌ | ❌ | ❌ | ✅ (Own Branch) | ✅ |
| **Configure Branch-Level Settings** | ❌ | ❌ | ❌ | ✅ (Own Branch) | ✅ |
| **Configure Global System Settings** | ❌ | ❌ | ❌ | ❌ | ✅ |
| **Access Financial Payout Logs** | ❌ | ❌ | ✅ (Own) | ✅ (Own Branch) | ✅ |

---

## Future Scope

* **Sub-accounts & Teams**: Allowing providers to delegate sub-roles (e.g., "Screen Operator" with read-only schedule downloads, "Billing Officer" with payout-only view).
* **Multi-Branch Staff Assignment**: Allowing a single staff user to switch contexts between multiple assigned branches.
