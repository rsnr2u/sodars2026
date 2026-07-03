# Customer Module: User Interfaces

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail visual layouts, forms, and dashboard layouts for the Customer Portal.

---

## Scope

This document specifies UI screens visible to advertisers:
* Customer Sign-Up.
* Customer Dashboard / Homepage.
* Order History & Invoice details.
* Profile & Billing Settings.
* Document Verification uploader.

---

## Business Rules

### 1. Screen Layout Specifications

#### Screen 1: Customer Sign-Up Form
* **Layout**:
  * Visual, dual-pane screen layout.
    * **Left**: Promo banner describing product value (USP, Google Maps first, POS bookings).
    * **Right**: Clean registration form.
  * Form inputs: Display Name, email, password, address, city, and a dropdown selecting "Individual", "Corporate", "Government", or "Political Party".
  * If "Government" or "Political Party" is selected, the form triggers an inline notification warning that document verification is required before campaigns can check out.

---

#### Screen 2: Customer Portal Dashboard
* **Layout**:
  * **Header**: Top navigation bar with cart and user dropdown.
  * **Rows Grid**:
    * **KPI cards Row**: Active campaigns count, total billing expenditure card (gross currency), pending review creatives count.
    * **Active Campaigns list**: Data table showing booking ID, targeted screen preview thumbnail, date flights, and status bar (e.g. Green "Active", Yellow "Pending review").
    * **Quick Navigation**: Large card redirects to "Go to Marketplace Map".

---

#### Screen 3: Order History & Invoices List
* **Layout**:
  * Data Table detailing all bookings transactions:
    * Order Date.
    * Booking Request UUID.
    * Total paid amount (Retail Price + Tax).
    * Status (`pending_audit`, `approved`, `cancelled`).
    * Download Action: Large touch-friendly button labeled **Download Invoice (PDF)**.

---

#### Screen 4: Verification Document Uploader
* **Layout**:
  * Standard profile sub-panel layout.
  * Dropzone area allowing PDF/PNG uploads.
  * Displays file list and audit status (e.g. "Pending Review", "Approved").

---

## Future Scope

* **Sub-accounts invites panel**: Multi-user permissions panel for agencies (deferred to V2).
