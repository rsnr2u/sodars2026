# Analytics Module: Business Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the business logic rules, constraints, data isolation limits, and auditing rules applied to Analytics.

---

## Scope

This document specifies the guidelines that all analytical reports, KPIs, and exports must meet, serving as permission logic specs for backend developers.

---

## Business Rules

### 1. Read-Only Constraint
* **No Database Modifications**: The Analytics module is strictly read-only. No analytical API endpoint or dashboard worker may create, update, or delete transaction data in other business modules.
* **Snapshot Caching Rules**:
  * Dashboard queries must pull metrics from pre-calculated cache snapshot tables (`analytics_snapshots`) to prevent performance degradation on transactional databases.
  * Live calculations are permitted only on direct export requests.

---

### 2. Authorization & Scoping
To protect system security and client data isolation, data queries must be filtered by user roles:

* **Provider Isolation**:
  * Providers can query and view only data matching their owned screens (`provider_id` matches user's company).
  * Accessing revenue logs or occupancy statistics of other providers is strictly prohibited.
* **Branch Manager Isolation**:
  * Branch Managers can query only data tagged with their assigned `branch_id`. Cross-branch comparisons are blocked.
* **Head Office (Super Admin)**:
  * Absolute access to query and aggregate data globally across all branches and providers.

---

### 3. Transaction Calculation Rules
* **Approved Transactions Only**:
  * KPIs (gross sales, conversions) must be aggregated from transactions in `Approved`, `In Progress`, or `Completed` states only. Draft or pending requests are excluded.
* **Exclusions**:
  * Cancelled bookings and rejected requests are excluded from all revenue totals.
* **Immutable Logs**:
  * Historical reports and exported datasets represent a permanent transactional state and must not change.
* **Export Auditing**:
  * Every file download execution (PDF/CSV/XLSX) must write a log record into the `analytics_exports` table capturing the requesting user, timestamp, file type, and filters parameters applied.

---

## Future Scope

* **Real-time API alerts on data anomalies**: Pinging branch managers if regional screen occupancy rates fall below a defined threshold (deferred to V2).
