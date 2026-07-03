# Provider Module: User Interfaces

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the visual layouts, panels, screen flow designs, and inputs required for the Provider dashboard and management portals.

---

## Scope

This document specifies UI screens visible to Providers and internal Admins:
* Provider Dashboard.
* Provider Profile & Settings.
* Subscription Card View.
* Staff Management panel.
* Document Upload & Auditing interface.
* Marketplace Settings.
* Activity Timeline.

---

## Business Rules

### 1. Screen Layout Specifications

#### Screen 1: Provider Dashboard
* **Objective**: Primary landing area logging earnings and active loop capacity.
* **Layout**:
  * **KPI Summary row**:
    * Lifetime Earnings (card showing gross currency).
    * Active screens / Max screens limit progress bar.
    * Current month payout status.
  * **Main Content Split**:
    * **Left**: Active bookings schedule calendar, showing blocks of purchased time slots.
    * **Right**: Recent activity notices (e.g., "New ad artwork uploaded for Screen downtown LED").

---

#### Screen 2: Provider Profile & Settings
* **Objective**: Edit corporate, address, and payout bank settings.
* **Layout**:
  * Tabbed card containers:
    * **Company Info Tab**: Form inputs for Name, Registration Number, address, and primary contacts.
    * **Bank Accounts Tab**: Form fields to register SWIFT/routing numbers, bank names, and account IDs.
    * **Marketplace Tab**: Checkbox controls toggling `enable_marketplace` settings globally.

---

#### Screen 3: Subscription Panel
* **Objective**: View current tier limits and execute upgrades.
* **Layout**:
  * **Header**: Shows current active plan (e.g., "Starter Tier: 2 Active Screens Limit").
  * **Grid**: 3 Pricing Cards:
    * *Free Starter*: ₹0 / month. Max 2 active screen listings.
    * *Standard*: Price card, max 20 active screens. Includes "Upgrade" call-to-action button.
    * *Unlimited*: Custom corporate price card.

---

#### Screen 4: Staff Panel
* **Objective**: Invite and assign operators.
* **Layout**:
  * Data Table listing staff members, emails, roles, and status (Active/Suspended).
  * Action button: "+ Add Staff Member". Opens a modal popup requesting name, email, and password.

---

#### Screen 5: Document Manager & Verification Panel
* **Objective**: Onload company credentials for auditing.
* **Layout**:
  * Dropzone card wrapper allowing PDF file drag-and-drop.
  * Documents table displaying:
    * Doc Type (e.g., Tax ID, ownership proof).
    * File name with download hyperlink.
    * Verification status badge (Yellow "Pending", Green "Approved", Red "Rejected").
    * Rejection comments panel (collapsible drawer).

---

#### Screen 6: Provider List (Admin View)
* **Objective**: Admin dashboard listing all providers.
* **Layout**:
  * Tabbed layout for `Pending Audits` and `All Providers`.
  * Grid showing company name, default branch, total screens, status badge, and an "Audits Profile" click route.

---

## Future Scope

* **Provider Analytics Performance Charts**: Charts demonstrating screen payout margins and traffic fluctuations over seasonal bounds.
