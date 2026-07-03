# Analytics Module: Workflows

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail operational workflows, calculations pipelines, and report scheduling steps for Business Intelligence.

---

## Scope

This document specifies step-by-step processes for:
* Loading dashboard stats (KPIs and charts).
* Aggregating financial totals and loop capacities.
* Generating weekly metrics snapshots.
* Running exports and scheduling dispatches.

---

## Business Rules

### 1. Workflow: Dashboard Load & Caching
* **Actor**: User (Admin, Provider, Customer).
* **Steps**:
  1. User accesses the dashboard.
  2. The system checks `analytics_cache` for matching key (e.g. `dashboard_stats_user_id`).
  3. If cache exists and is not expired:
     * System loads and renders JSON.
  4. If cache is missing or expired:
     * System executes target database queries, aggregates data matching user bounds.
     * System writes updated JSON to `analytics_cache` with a 15-minute expiration period.
     * System renders results on dashboard.

---

### 2. Workflow: Nightly Snapshot Generation (Generate KPIs)
* **Actor**: System (Cron Job).
* **Steps**:
  1. Cron task triggers nightly at 01:00 AM.
  2. System scans preceding day's logs across modules:
     * *Revenue Calculation*: Sums paid bookings (`booking_items.retail_price_cents` where status is approved or completed, excluding cancelled).
     * *Occupancy Calculation*:
       $$\text{Occupancy \%} = \left( \frac{\text{Sum of Booked slots}}{\text{Total Screen Capacities}} \right) \times 100$$
     * *Conversion Rates*: Calculate total booking requests created divided by total public search click logs.
  3. Inserts pre-computed KPI metrics into `analytics_snapshots`.

---

### 3. Workflow: Export Report
* **Actor**: User.
* **Steps**:
  1. User selects report type (e.g. "Revenue Report"), configures filters, and clicks **Export**.
  2. User selects format (PDF, Excel, CSV).
  3. System checks permissions.
  4. System executes query, streams dataset, compiles into target format, and starts browser download.
  5. System records log entry in `analytics_exports` capturing modifier details and target file scopes.

---

### 4. Workflow: Scheduled Reports Dispatch
* **Actor**: System (Cron Job).
* **Steps**:
  1. Weekly/Monthly scheduler cron runs.
  2. Finds active entries in `scheduled_reports`.
  3. For each active schedule:
     * Compiles report dataset.
     * Renders data into template layout (`report_templates`).
     * Compiles PDF file.
     * Dispatches transactional email with PDF file attachment to target recipient address.

---

## Future Scope

* **Real-time stream aggregation**: Using queue processors to aggregate live play metrics on IoT players (deferred to V2).
