# Inventory Module: Workflows

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the operational workflows and logic steps for creating, verifying, managing, and search-filtering digital screen assets.

---

## Scope

This document specifies step-by-step workflows for:
* Creating inventory listings and uploading photos/geocodes.
* Managing screen approvals.
* Assigning Providers and routing managing Branches.
* Toggling public marketplace visibility.
* Adjusting baseline and custom seasonal pricing.
* Executing inter-branch screen transfers.
* Checking calendar date availability for bookings.

---

## Business Rules

### 1. Workflow: Create Inventory
* **Actor**: Provider Admin.
* **Steps**:
  1. Provider clicks **Add New Screen**.
  2. Input specs: Screen Name, Media Type, Width, Height, Orientation, Uptime schedules, estimated traffic, and baseline daily **Net Price**.
  3. Enter physical address. System calls Google Places Autocomplete API.
  4. System captures address coordinates:
     * *Validation*: GPS coordinates (Latitude & Longitude) must be successfully geocoded and non-zero.
  5. System resolves branch boundaries:
     * Queries `branch_coverage_cities` matching the geocoded city name.
     * Mapped `branch_id` is linked to the new screen.
  6. Asset is written to database in `draft` status.

---

### 2. Workflow: Upload Photos & Documents
* **Actor**: Provider Admin.
* **Steps**:
  1. Provider uploads photos (jpeg/png) of physical installation site and compliance certificates (e.g. municipality permits).
  2. System validates files:
     * *Validation*: Photo files must be smaller than 10MB.
     * *Validation*: Document files must be PDF/PNG formats smaller than 20MB.
  3. Files are saved directly to S3 bucket keys. References are logged to `inventory_media` and `inventory_documents` tables.
  4. Provider clicks **Submit for Review**. Status changes to `Pending Approval`.

---

### 3. Workflow: Approve / Reject Inventory
* **Actor**: Branch Manager.
* **Steps**:
  1. Branch Manager views pending list.
  2. Audits photos, geocodes, and permit certificates.
  3. Manager clicks **Approve** or **Reject**:
     * *Approve Action*: Status switches to `approved`. The screen is published to the public marketplace index cache.
     * *Reject Action*: Status reverts to `draft`. The manager must enter a text description explaining why (e.g., "Municipal permit expired"). Provider is notified by email.

---

### 4. Workflow: Toggle Marketplace Visibility
* **Actor**: Provider Admin / Branch Manager.
* **Steps**:
  1. Provider Admin toggles "Active Marketplace Listing" switch.
  2. System checks `enable_marketplace` flag. If `0`:
     * The asset is hidden from the public map.
     * Ongoing bookings continue to play. New booking queries fail.

---

### 5. Workflow: Change Rates
* **Actor**: Provider Admin.
* **Steps**:
  1. Provider clicks **Manage Rates** on an approved screen.
  2. Options:
     * *Change Baseline*: Enter new daily Net Price. Applies to all future bookings.
     * *Add Custom Period*: Enter custom Net Price, Start Date, and End Date (e.g., Festival pricing).
  3. System validates:
     * *Validation*: Custom pricing dates must not overlap.
  4. System inserts custom rate records.

---

### 6. Workflow: Archive Inventory
* **Actor**: Provider Admin.
* **Steps**:
  1. Provider clicks **Delete Screen**.
  2. System checks calendar availability:
     * *Validation*: If the screen has paid upcoming bookings scheduled, deletion is blocked. User must cancel bookings first.
  3. Status changes to `archived`. The database record is soft-deleted (`deleted_at` timestamp is written).

---

### 7. Workflow: Availability Check (Double-Booking Prevention)
* **Actor**: System (Checkout Engine).
* **Steps**:
  1. Customer adds screen to booking cart for target flight dates: `2026-10-01` to `2026-10-10`.
  2. For each date in the range, the system queries the `inventory_availability` table.
  3. *Validation Rule*:
     * If `slots_booked` plus new slots requested exceeds the screen's maximum capacity (e.g., 6 slots/day), checkout fails. Customer is alerted that the screen is sold out for those dates.
  4. If validation succeeds, system updates `slots_booked` and locks inventory for payment checkout.

---

## Future Scope

* **Real-Time AI verification of geocodes**: Automated validation verifying that GPS coordinates match Street View data (deferred to V2).
