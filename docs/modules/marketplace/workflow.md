# Module: Marketplace: Workflows

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the operational workflows and logic steps for the client-facing Marketplace module, covering user discovery to booking request submission.

---

## Scope

This document specifies step-by-step workflows for:
* Opening the marketplace, searching by geocodes, and browsing on Google Maps.
* Filtering display search results.
* Comparing multiple displays.
* Adding items to the POS booking cart.
* Submitting booking requests, coordinating branch audits, and confirming availability.

---

## Business Rules

### 1. Workflow: Search & Google Maps Discovery
* **Actor**: Public Guest / Customer.
* **Steps**:
  1. User opens the homepage.
  2. The system loads Google Maps:
     * *Default Coordinates*: Centers on the user's browser location or default system coordinate.
  3. System retrieves screen marker details:
     * Queries database for approved, marketplace-enabled screens near the map boundary coordinates.
     * Computes the Retail Price for each screen (locked Net Price + Branch Markup).
  4. System plots pins as markers on the map.
  5. User pans/zooms on the map:
     * System fires an API query to fetch newly visible screens inside the adjusted map bounding coordinates.
     * Pins are updated dynamically on the map.

---

### 2. Workflow: Apply Search Filters
* **Actor**: Customer.
* **Steps**:
  1. Customer toggles filter options in the sidebar (e.g. Orientation: Portrait, Daily Price: ₹1,000 - ₹5,000, Media Type: LED Screen, Availability Range: 2026-10-01 to 2026-10-07).
  2. System filters results:
     * *Pricing Logic*: Matches target Retail Prices (Net Price + Markup).
     * *Availability Logic*: Checks if the sum of booked slots in `inventory_availability` plus requested slots is less than maximum daily loop slots on the target dates.
  3. Re-renders markers on the map and updates the search results list view.

---

### 3. Workflow: Compare Inventory
* **Actor**: Customer.
* **Steps**:
  1. Customer selects the "Compare" checkbox on multiple display listing cards (maximum of 3 displays).
  2. Clicking **Compare Now** opens a overlay grid.
  3. System renders attributes side-by-side:
     * Aspect Ratio / Pixel Resolutions.
     * Uptime schedules and orientation.
     * Estimated daily traffic reach.
     * Total cost for selected dates (calculating retail values).
  4. Customer clicks "Add to Cart" directly from the compare table.

---

### 4. Workflow: Booking Request & Verification Flow
* **Actor**: Customer / Branch Manager / Provider / System.
* **Steps**:
  1. Customer configures their cart and clicks **Submit Booking Request**.
  2. Customer uploads target creative artwork assets and completes payment checkout (payments are held in authorization state/escrow).
  3. System generates a `Booking Request` record in `pending_audit` status.
  4. **Branch Review Gate**:
     * Branch Manager views the booking request in their dashboard.
     * Verifies payment status and checks if the uploaded ad artwork complies with local content policies.
     * If rejected: Payout authorization is cancelled; customer is notified of content rejection.
     * If approved: Branch Manager clicks **Validate Request**. Status shifts to `pending_provider_confirm`.
  5. **Provider Confirmation Gate**:
     * Provider receives email/dashboard alert.
     * Provider logs in and confirms that the physical screen can execute the schedule.
     * Provider clicks **Accept Booking** -> Booking shifts to `Approved / Paid`.
     * System writes dates slot reservations to the `inventory_availability` table and triggers campaign generation.
  6. **Customer Notification**:
     * System dispatches email to the Customer confirming the campaign is scheduled.

---

## Future Scope

* **Instant Auto-Approval Engine**: Bypassing manual provider validations for automated networks (deferred to V2).
