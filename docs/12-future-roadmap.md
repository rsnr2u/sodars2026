# 12. Future Roadmap

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to list, organize, and specify features, modules, and architecture changes deferred to Version 2 and beyond. It prevents scope creep and documents ideas for future phases.

---

## Scope

This document details:
* Future feature blueprints (AI features, Artwork generators, Agency portals, Franchise models).
* Technical scaling plans (automated telemetry, programmatic RTB).
* Rules for moving items from the Future Roadmap into active development.

---

## Business Rules

### 1. Future Feature Specifications

* **Artificial Intelligence (AI) Features**:
  * **Audience Recommendations**: AI model analyzing geographic census data, traffic flows, and advertiser category to auto-recommend a bundle of screen locations.
  * **Dynamic Pricing Engine**: Automated adjustment of markup percentages based on real-world events, localized traffic hikes, or high historical occupancy.
* **Integrated Artwork Design Canvas**:
  * Web-based drag-and-drop graphic designer allowing Customers to build their ad banners or video templates directly within the Customer Portal.
* **Agency Portal & Multi-Client Accounts**:
  * Specialized accounts for marketing agencies.
  * Support for managing distinct sub-accounts, unified invoicing, line-of-credit billing, and bulk booking discounts.
* **Franchise Branch System**:
  * Splitting Branch ownership to independent franchise partners.
  * Automated payment routing splitting the platform commission fee (e.g., 20% total fee split: 5% to SODARS Head Office, 15% to Franchise Branch Owner).
* **Live Proof-of-Performance (PoP)**:
  * Integration with physical screen webcams to capture proof of display.
  * Real-time logs showing exact play duration and play counters via client-side player integrations.

### 2. Governance Rule
* No code for features in this document may be written during the Version 1 implementation phases.
* To move an item from this document into active development, it must be drafted into a dedicated module architecture proposal, approved by the Head Office, and integrated into `03-version1-scope.md` and the database/API guidelines.

---

## Future Scope

* This document itself will grow as business requirements evolve. All stakeholder requests for post-V1 enhancements must be appended here.
