# ADR 001: Branch-Based Business Model

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 001: Branch-Based Business Model
* **Status**: Approved
* **Date**: 2026-06-29

---

## Context

We need to establish the operational business model structure for SODARS. We must decide whether to expand via direct internal business divisions (Branches), decentralized franchising (Franchises), or independent distributor agreements (Partners). 

The chosen model directly impacts:
1. Ownership of data and revenue splits.
2. Responsibility for local digital display verification and provider onboarding.
3. Level of software complexity (multi-tenant ledger splits vs single corporate ledger division).

---

## Decision

SODARS will utilize a direct **Branch-based business model** for Version 1. 

* **Direct Ownership**: SODARS owns and operates all branches. Branches are internal business units rather than third-party franchisees.
* **Roles Separation**:
  * **Branches**: Function as local corporate operations hubs responsible for governing local providers, approving screen inventories, and managing local customer disputes.
  * **Providers**: Participate as platform subscribers who list their digital screen inventory.
  * **Customers**: Purchase advertising services on these screens through the public marketplace.
* **Expansion Mechanics**: Future market expansion is achieved directly by opening new branches inside the system (under direct corporate command), rather than selling franchise territories.

---

## Consequences

* **Advantages**:
  * Simpler accounting architecture: All payments route to a single global merchant account and payout to providers, avoiding complex split-payment commission formulas.
  * Complete operational quality control: SODARS maintains consistent standards for screen approvals, billing, and support.
  * Strict database isolation: Regional branches are partitioned easily using a simple `branch_id` query scope.
* **Disadvantages**:
  * Scaling requires SODARS to invest capital to set up regional physical managers and operations, rather than leveraging franchisee capital.

---

## Future Notes

* If the business scales globally, we can evaluate a Franchise or Affiliate model in Version 2. 
* The database schemas created in Phase 2 should make no assumptions about split payouts, keeping tables structured around internal branches.
