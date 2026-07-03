# ADR 004: Version 1 Scope Exclusions

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 004: Version 1 Scope Exclusions
* **Status**: Approved
* **Date**: 2026-06-29

---

## Context

We need to establish boundaries to prevent scope creep and limit capital expenditures. Building features like AI recommenders, custom graphics design generators, agency portal divisions, and automated franchise payout systems requires significant time and complex software integrations.

---

## Decision

Version 1 of SODARS intentionally excludes:
* **Artificial Intelligence (AI)**: Auto-scheduling slots and audience reach forecast maps.
* **Design Generator**: Integrated banner artwork builder interfaces.
* **Agency Module**: Multi-tenant client structures, volume discounts, and monthly credit term billings.
* **Franchise Module**: Split payouts between regional franchise units and the global head office.
* **Enterprise Features**: Deep programmatic DSP/SSP real-time bidding integrations or ERP/CRM connections.
* **Advanced Analytics**: External traffic monitors, video playback sensor loops, and complex charting logs.

**Rationale**: Version 1 focuses exclusively on launching a minimum viable product successfully to validate the fundamental business model, verify provider participation, and test customer marketplace checkout flows.

---

## Consequences

* **Advantages**:
  * Faster speed to market: Reduced timeline from concept to deployment.
  * Simpler, cleaner software code: Focuses on core workflows without complex API dependencies.
  * Measurable market validation: Real customer checkout transactions will validate product demand before investing in complex automation.
* **Disadvantages**:
  * Large marketing agencies or independent franchise operators will have to wait for future releases to participate in the ecosystem.

---

## Future Notes

* All excluded items are cataloged in `docs/12-future-roadmap.md` and will be prioritized for Version 2 based on post-launch analytics.
