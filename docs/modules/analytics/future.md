# Analytics Module: Future Scope

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to list future requirements and business features for Analytics that are out of scope for Version 1.

---

## Scope

This document specifies:
* Out-of-scope predictive analytics utilities.
* Future integrations with external enterprise BI platforms.

---

## Business Rules

### 1. Deferred Features (Out of Scope for V1)

* **Predictive Analytics & AI Forecasting**:
  * Machine learning algorithms projecting future quarterly revenues based on historical patterns.
* **Demand & Pricing AI Predictions**:
  * Suggesting optimal screen pricing markups dynamically in anticipation of localized demand changes (e.g. holidays or sports events).
* **AI Insights & Anomaly Detection**:
  * Auto-generating text observations on dashboards (e.g. "Mumbai branch sales increased 14% due to high transit media purchases").
  * System alerts flagging billing anomalies.
* **Enterprise Data Warehouse Syncing**:
  * Automated ETL (Extract, Transform, Load) pipelines to sync snapshots with Google BigQuery, Snowflake, or AWS Redshift.
* **Power BI, Google Looker Studio & Tableau Connectors**:
  * Providing API endpoints or read-only database views optimized for direct connection with enterprise reporting platforms.
* **Real-time Live Telemetry Dashboards**:
  * Tracking display heartbeats, active visual logs, and physical screen player outages in real-time.

---

## Future Scope

* Re-evaluate these requirements during Version 2 scoping sessions.
