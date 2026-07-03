# Module: Customers

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the Customer module, which governs advertiser user profiles, entity classification (Individual, Corporate, Government, Political), order diaries, and secure invoice downloads.

---

## Scope

This document specifies:
* Customer profile definitions and responsibilities.
* Entity classification criteria.
* Relationships between Customers, default billing branches, and booking slots.
* Account lifecycle parameters.

---

## Business Rules

### 1. Customer Overview & Responsibilities
* **Definition**: Customers are advertisers (brands, individuals, public departments, political entities) who use the public marketplace to select screens and purchase ad loop capacity.
* **Core Responsibilities**:
  * Self-register accounts and select correct entity type tags.
  * Supply verified tax certificates or authorization letters if registering as Government or Political units.
  * Pay invoice balances via Stripe/Razorpay or direct bank transfer receipts.
  * Upload creative ad media assets conforming to screen spec sheets.

---

### 2. Customer Classifications (Version 1)
To ensure compliance with local advertising regulations, the system distinguishes between four entity classes:

* **Individual**: Local citizens booking notices, greetings, or announcements. (Instant checkout enabled).
* **Corporate/Business**: Commercial enterprises promoting goods and services. (Instant checkout enabled).
* **Government/NGO**: Public utility notices, civil warnings, or department alerts. (Approval lock triggered: Requires manual authorization documents audit before bookings activate).
* **Political Party**: Election campaigns, political notices. (Approval lock triggered: Requires compliance document validation before bookings activate).

---

### 3. Customer Lifecycle States
* **Active**: Default registration state. Customer can browse, cart, and check out screens.
* **Suspended**: Triggered by Branch Managers or Admins (e.g. for non-compliant creative submissions or billing failures). Logins are active but checkout actions are blocked.
* **Deactivated**: Soft-deleted state. Account logins are disabled.

---

## Future Scope

* **Agency Account Splits**: Enabling advertising agencies to invite distinct sub-clients and check out campaigns from a unified billing wallet (deferred to V2).
