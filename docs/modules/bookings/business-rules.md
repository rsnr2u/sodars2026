# Booking Module: Business Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the business logic rules, constraints, validations, and payment options applied to Bookings.

---

## Scope

This document specifies the rules governing order submissions, availability checks, payments records, and campaign activations.

---

## Business Rules

### 1. Booking Integrity & Approval Rules
* **No Approval Bypass**: Every booking request must traverse the branch and provider validation pipeline. No booking can bypass approval gates.
* **Locked Pricing**: Once a booking request is submitted, its pricing configuration (Net Price, Branch Markup, and Retail Price) is copied and locked in `booking_items`. Subsequent edits to display pricing do not alter the checkout values.
* **No Post-Approval Edits**: Customers cannot modify flight dates, targeted screens, or slot frequencies of a booking once its status is changed to `Approved`. Any changes require cancelling the booking and placing a new request.
* **Campaign Triggers**: Campaigns are created *only* after a booking status transitions to `Approved`.

---

### 2. Inventory Availability & Release Rules
* **Double-Booking Prevention**:
  * The system must execute availability checks on `inventory_availability`.
  * If a screen date has reached maximum loop capacity, checkout queries must return a validation error.
* **Cancellation Release**:
  * Transitioning a booking to `Cancelled` must instantly decrement the date booking count in `inventory_availability`, releasing the slots to other customers.

---

### 3. Payment Logging Rules (Version 1)
* **Offline Records Only**: Version 1 does not support integrated payment gateway checkout APIs. Payments must be recorded manually.
* **Supported Offline Methods**:
  * **Cash**: Handled at local branch offices.
  * **Bank Transfer**: NEFT, RTGS, IMPS.
  * **Cheque**: Requires bank clearance audit.
  * **UPI**: Static QR scanner code transfers.
* **Validation Control**: A payment record must contain a unique transaction reference number and is logged by a specific authorized Branch Manager.

---

### 4. Immutable Records
* **Reconciliation Auditing**: Completed and Approved booking items and pricing records are immutable.
* **Status History Log**: Every status transition must log a record to `booking_status_history` detailing the modifier user ID, previous/new states, timestamps, and comments.

---

## Future Scope

* **Automatic Gateway Webhook Auditing**: Auto-clearing payments upon Stripe payment notifications (deferred to V2).
