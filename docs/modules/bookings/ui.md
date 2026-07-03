# Booking Module: User Interfaces

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the visual layouts, panels, screen flow designs, and inputs required for the Booking module in all user portals.

---

## Scope

This document specifies UI screens visible to Customers, Providers, and Admins:
* Booking List views.
* Booking Details & Audit Timeline.
* Payment recording interface.
* Print-ready Invoice view.

---

## Business Rules

### 1. Screen Layout Specifications

#### Screen 1: Booking List
* **Provider/Branch View**:
  * Tabbed data table detailing transactions:
    * Tabs: `Awaiting Payout/Confirm`, `Approved`, `Cancelled`, `Completed`.
    * Columns: Booking ID, Customer Name, Total Price, Flight Start/End, Status Badge.
* **Customer View**:
  * List of booking card containers, displaying screen cover thumbnails, dates, price tags, and status bar.

---

#### Screen 2: Booking Details & Status Timeline
* **Objective**: View individual booking parameters and audit logs.
* **Layout**:
  * **Main Content Split**:
    * **Left Pane**: Mapped list of displays selected, showing cover thumbnail, location details, date range, play loops frequency, and line-item cost.
    * **Right Pane (Sticky)**:
      * Standard **Workflow Status Tracker**:
        ```text
        [x] Submitted -> [x] Payment Recorded -> [ ] Branch Approved -> [ ] Provider Confirmed
        ```
      * Transaction summary cards showing Gross Cost, tax rates, payment reference logs, and creative video preview widget.
      * Action triggers: **Validate Payment**, **Approve Booking**, **Reject Booking** (opens rejection textarea modal).

---

#### Screen 3: Payment Details Form (Admin Views Only)
* **Objective**: Manual transaction logging.
* **Layout**:
  * Triggered by clicking "Record Payment".
  * Dropdown selector: Payment Method (UPI, bank ledger, cash, cheque).
  * Text inputs: Payment Reference number (mandatory), Verified Amount box (auto-filled with booking total).
  * Date Picker: Date transaction was cleared.
  * Submit Action: "Apply Payment Verification".

---

#### Screen 4: Print-Ready Invoice View
* **Objective**: Standard printable invoice presentation.
* **Layout**:
  * Minimalist layout (black/white optimized styling with clean fonts).
  * System metadata header (SODARS Logo, branch name, tax registry code).
  * Customer bill-to coordinates.
  * Booking items details matrix:
    * Screen details, date flight duration, loops frequency, retail unit price, subtotal.
  * Footer: Payment reference numbers, verification timestamp, tax rate declarations, total paid figures.
  * Floating action: "Print Invoice / Download PDF".

---

## Future Scope

* **Stripe Hosted Checkout Page overlay**: Direct modal embedding for online processing (deferred to V2).
