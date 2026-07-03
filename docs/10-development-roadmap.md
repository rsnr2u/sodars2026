# 10. Development Roadmap

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to outline the sequential phases of development for SODARS Version 1, detailing the milestones and exit criteria for each step.

---

## Scope

This document details:
* The 10 phases of the development lifecycle.
* The focus areas and deliverables of each phase.
* Progression rules between phases.

---

## Business Rules

### 1. Phased Roadmap Plan

```mermaid
gantt
    title SODARS Version 1 Development Timeline
    dateFormat  YYYY-MM-DD
    section Backend Core
    Phase 1: Laravel Project & Branch Module :active, des1, 2026-06-29, 7d
    Phase 2: Provider Module APIs            :      des2, after des1, 5d
    Phase 3: Inventory Module APIs           :      des3, after des2, 7d
    Phase 4: Marketplace APIs                :      des4, after des3, 5d
    section Portals & Engine
    Phase 5: Customer Portal (Next.js)       :      des5, after des4, 10d
    Phase 6: Booking Engine                  :      des6, after des5, 8d
    Phase 7: Campaign Module                 :      des7, after des6, 8d
    Phase 8: Admin Panel (React + Vite)      :      des8, after des7, 10d
    Phase 9: Provider Portal (React + Vite)  :      des9, after des8, 10d
    section Mobile & Launch
    Phase 10: Mobile App (React Native)      :      des10, after des9, 15d
```

* **Phase 1: Laravel Project, Auth & Branch Module**:
  * Set up Laravel API repository framework. Configure Sanctum, database structure, seed scripts, RBAC, and Branch APIs.
  * Deliverable: Working backend project shell and passing Branch API tests.
* **Phase 2: Provider Module**:
  * Set up Provider onboarding, profile, secure bank credentials update, and compliance document upload APIs.
  * Deliverable: Covered Provider management API endpoints.
* **Phase 3: Inventory Module**:
  * Configure screen inventory, geocoding coordinates validation, S3 photo uploads, and rates schedules APIs.
  * Deliverable: Covered Inventory management and pricing API endpoints.
* **Phase 4: Marketplace APIs**:
  * Construct map search query scopes, coordinates clustering, and filtered display searches.
  * Deliverable: Public Marketplace discovery API endpoints.
* **Phase 5: Customer Portal (Next.js)**:
  * Build the public customer website, Google Maps-first UI, display preview sliders, and customer login interfaces.
  * Deliverable: Responsive Customer web frontends.
* **Phase 6: Booking Engine**:
  * Develop POS-style checkouts, recalculation services, transaction tracking, invoice builders, and payment integrations.
  * Deliverable: Cart, payment processing, and request ledger workflows.
* **Phase 7: Campaign Module**:
  * Build the campaign management panel. Implement media file validators, flight schedulers, and creative audits trackers.
  * Deliverable: Operational campaign scheduling dashboard.
* **Phase 8: Admin Panel (React + Vite)**:
  * Construct internal SPA portal for Super Admins and Branch Managers to audit providers and approve screens.
  * Deliverable: Complete Admin dashboard portal.
* **Phase 9: Provider Portal (React + Vite)**:
  * Create portal UI for display owners to view booking charts, payouts schedules, and manage screens.
  * Deliverable: Integrated Provider dashboard portal.
* **Phase 10: Mobile App (React Native)**:
  * Build the companion smartphone app for GPS checks, photo audits, and active metrics check.
  * Deliverable: Compiled iOS and Android mobile apps.

### 2. Phase Gates
* A phase cannot be declared complete until its designated test suite or manual checklist achieves 100% success.
* Developers must refer to `11-coding-standards.md` to ensure standard linting rules are applied at every stage.

---

## Future Scope

* Introduction of continuous blue-green zero-downtime deployment pipelines in Phase 10.
