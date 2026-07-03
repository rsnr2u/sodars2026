# Booking Module: Workflows

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the operational workflows and logic steps for processing Booking Requests, validation, manual payments, and approvals.

---

## Scope

This document specifies step-by-step workflows for:
* Submitting booking requests.
* Checking slot availability.
* Running Branch Manager audits and recording offline payments.
* Coordinating Provider approval reviews.
* Processing cancellations and release blocks.
* Auto-triggering Campaign and Invoice generation.

---

## Business Rules

### 1. Workflow: Create Booking Request
* **Actor**: Customer.
* **Steps**:
  1. Customer views Cart and clicks **Checkout**.
  2. Input: Uploads creative ad creative, enters offline payment reference details (e.g. Bank reference).
  3. System initiates checks:
     * *Validation*: Cart items must not be empty.
     * *Validation*: All items must map to active screens.
  4. System runs **Validate Inventory Availability**:
     * Queries database to verify if screen loop capacities can support the dates.
  5. If validation passes, system:
     * Generates a new UUID `booking_id`.
     * Copies screen pricing coordinates into `booking_items`.
     * Inserts the booking in `pending` status.
     * Logs coordinates to `booking_status_history`.

---

### 2. Workflow: Branch Review & Payment Recording
* **Actor**: Branch Manager.
* **Steps**:
  1. Branch Manager checks "Pending Bookings" list.
  2. Reviews uploaded payment reference details:
     * Manager verifies receipt in bank ledger statement.
     * Manager clicks **Record Payment**:
       * Input: Selects Payment Method (UPI, NEFT, cash, etc.), enters verified amount, and logs details.
       * System creates a record in `booking_payments` in `verified` status.
  3. Manager reviews uploaded creative ad content.
  4. If payment and creative are verified:
     * Manager clicks **Submit to Provider**.
     * Booking status changes to `provider_review`. Provider is notified.

---

### 3. Workflow: Provider Approval
* **Actor**: Provider Admin.
* **Steps**:
  1. Provider logs in and inspects the screen booking request.
  2. Confirms physical display uptime and schedule slot allocation.
  3. Actions:
     * **Approve**: Provider clicks **Confirm Availability**.
       * Status transitions to `Approved`.
       * System calls **Generate Campaign** (copies details to Campaign module).
       * System calls **Generate Invoice** (PDF).
       * Date slots are locked in `inventory_availability`.
     * **Reject**: Provider clicks **Reject Booking**.
       * Provider enters text reason.
       * Status transitions to `Rejected`.
       * Customer is notified to select alternate screens or dates.

---

### 4. Workflow: Booking Cancellation
* **Actor**: Customer / Branch Manager / Admin.
* **Steps**:
  1. Authorized user triggers **Cancel Booking**.
  2. System changes status to `Cancelled`.
  3. System releases date slots by updating the `inventory_availability` database table, freeing capacity for other searchers.
  4. If payment was verified:
     * Refund status is set to pending (for manual bank payout resolution in V1).

---

## Future Scope

* **Autopay Loop Verification**: Auto-confirming bookings on receipt of payment hook notifications (deferred to V2).
