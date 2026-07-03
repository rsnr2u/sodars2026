# Marketplace Module: Business Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the business logic rules, data visibility limits, pricing policies, and approval pipelines applied to the customer-facing Marketplace.

---

## Scope

This document specifies the validation criteria for search indexing, pricing computations, and booking checks.

---

## Business Rules

### 1. Asset Indexing & Visibility Constraints
* **Approved Status**: Only inventory records with `status = "approved"` can be queried or displayed on the public marketplace map. Draft, pending, suspended, or archived listings must be excluded from public APIs.
* **Visibility Enablement**: The screen's `enable_marketplace` flag and the owner provider's `enable_marketplace` settings profile must both equal `1`.
* **Asset Data Mandates**: Any screen listing appearing in search results must possess:
  * Non-zero geocoded Latitude and Longitude values.
  * At least one verified site photograph designated as primary.

---

### 2. Pricing Transparency & Secrets
* **Net Price Secrecy**: Customers (advertisers) must never see the Provider's Net Price or the platform's markup percentages in search lists, drawers, carts, or checkout receipt cards.
* **Marketplace Price Presentation**: Customers see only the final computed Retail Selling Price.
* **Markup Thresholds**:
  * The Retail Price is derived using the branch-configured markup percentage.
  * Markup percentages must follow database configurations (default max 20%), dynamically fetched from Settings.

---

### 3. Campaign Requests Workflow (No Instant Confirmation in V1)
* **Request Status**: Completing checkout does not instantly activate a booking. It registers a **Booking Request** in `pending_audit` status.
* **Audit Pipeline**:
  * **Branch Audit**: The governing Branch Manager verifies transaction payments and reviews uploaded creative ad content.
  * **Provider Audit**: The Provider confirms screen operational uptime and loop schedule slots.
  * **Activation**: Only after both validations succeed does the booking status transition to `Approved / Active`, scheduling campaign loop slots in `inventory_availability`.

---

## Future Scope

* **Instant Autopay Checkouts**: System automatically processing slot reservations without manual audits for verified customers (deferred to V2).
