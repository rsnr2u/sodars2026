# 03. Version 1 Scope

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to lock down the deliverables for the initial release (Version 1) of SODARS, ensuring developers do not spend resources on future or speculative feature sets.

---

## Scope

This document details:
* In-scope features and capabilities for Version 1.
* Explicitly out-of-scope modules deferred to later phases.
* The boundaries of initial stakeholder workflows.

---

## Business Rules

### 1. In-Scope Modules (Version 1)

* **Branches & Settings**:
  * Create regional branches and assign branch managers.
  * Define global settings, default markup percentages, and active cities.
* **Provider Management**:
  * Provider self-registration and dashboard showing booking orders.
  * Simple screen inventory management (add/edit digital screens, specifications, daily runtime hours).
* **Inventory Management**:
  * Digital display attributes: Location coordinates, size/resolution, supported file formats, pricing, operating schedule.
  * Branch approval workflow: State machine changes from `Pending` -> `Approved` or `Rejected` with comments.
* **Marketplace Discovery**:
  * Public Google Maps search interface.
  * Multi-filter search (by branch, price, display type, resolution).
* **POS-Style Booking Cart**:
  * Select screens on map -> Add to cart.
  * Define campaign dates, slot duration (e.g., 10-second slots), and repeat frequency (e.g., every 5 minutes).
  * Direct pricing display with markup calculation.
  * Checkout and upload ad creative (support for image and video formats).
* **Simple Analytics**:
  * Financial summaries: Total sales, payouts, platform revenue.
  * Occupancy rate metrics for digital assets.
* **Basic Notifications**:
  * System-generated emails for booking confirmations, approval alerts, and file uploads.

### 2. Out-of-Scope Modules (Deferred to Future Versions)

* **Artificial Intelligence (AI)**:
  * Smart recommendation engines or heatmaps predicting target audience reach.
  * Auto-scheduling slot optimization algorithms.
* **Design/Artwork Generator**:
  * Dynamic canvas editors or auto-generating visual collateral inside the portal.
* **Agency Portal**:
  * Bulk agency accounts managing multiple client accounts, complex invoice terms, and sub-billing.
* **Franchise Modules**:
  * Franchise-specific automated split payouts, franchise territory limits, and licensing fees.
* **Advanced Analytics & Telemetry**:
  * Real-time camera feeds, device heartbeats/ping monitoring, and automated loop check integrations.
* **Enterprise ERP Integrations**:
  * SAP, Oracle, or third-party CRM programmatic advertising platforms (DSP/SSP).

---

## Future Scope

* Re-evaluating the features listed under "Out-of-Scope" systematically during Version 2 planning.
