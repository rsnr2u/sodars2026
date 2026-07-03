# Inventory Module: Business Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the business logic rules, constraints, validations, and search filters governing digital assets.

---

## Scope

This document specifies the guidelines that all screen listings must meet before publication, mapping validation metrics and search criteria.

---

## Business Rules

### 1. Data Integrity & Mandatory Attributes
* **Provider Ownership Lock**:
  * Every screen belongs to exactly **one Provider**. This ownership link is static and can never be modified.
* **Managing Branch Boundaries**:
  * Every screen belongs to exactly **one Managing Branch**.
  * If operational bounds shift, branch managers or admins can update the screen's `branch_id`.
* **GPS Coordinate Mandate**:
  * An inventory item must contain precise Geolocation Coordinates (Latitude and Longitude). Creation validation fails if either coordinates are missing or zero.
* **Photo Verification Mandate**:
  * To change listing status from `draft` to `pending_review`, the provider must upload at least one physical site photograph.
  * A screen can have multiple media files, but exactly one photo must be set as `primary = 1` to serve as the search cover photo.

---

### 2. Rate & Pricing Intervals
* Providers define a baseline daily Net Price.
* Multiple pricing rate intervals (custom seasonal dates) are permitted.
* If custom date rates exist:
  * The pricing query logic must load the custom rate matching the checkout dates.
  * If no custom rates match, default baseline prices apply.

---

### 3. Search Filters Specification
The system must support the following search criteria:

| Filter Parameter | Target Column / Relation | Logic Behavior |
| :--- | :--- | :--- |
| **State** | `inventories.state` | Exact string match. |
| **District** | `inventories.district` | Exact string match. |
| **City** | `inventories.city` | Exact string match. |
| **Area** | `inventories.area` | Partial text matching. |
| **Media Type** | `inventories.media_type` | Exact lookup (e.g., `led_screen`). |
| **Category** | `inventory_categories.name` | Matching categories pivot lookup. |
| **Availability** | `inventory_availability` | Date availability query (exclude fully booked dates). |
| **Price Range** | `inventories.net_price_cents` | Range filter matching the branch-marked Retail Price. |
| **Size** | `inventories.width_cm`, `height_cm` | Filter matching size metrics. |
| **Orientation** | `inventories.orientation` | Lookup matching `portrait` or `landscape`. |
| **Illuminated** | `inventories.illuminated` | Boolean lookup. |
| **Marketplace Enabled**| `inventories.enable_marketplace`| Must equal `1` for public queries. |
| **Branch** | `inventories.branch_id` | Mapped branch boundaries query. |
| **Provider** | `inventories.provider_id` | Screen owner lookup. |

---

## Future Scope

* **Automatic Overlap Audits**: AI audits flag screen double-bookings across regional districts (deferred to V2).
