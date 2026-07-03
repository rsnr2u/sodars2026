# Analytics Module: Database Design

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the relational database schemas, columns, constraints, foreign key mappings, and indexes for the Analytics module of SODARS.

---

## Scope

This document specifies seven tables: `analytics_snapshots`, `analytics_cache`, `analytics_reports`, `analytics_exports`, `dashboard_widgets`, `report_templates`, and `scheduled_reports`. It does not duplicate fields from core transaction tables.

---

## Business Rules

### 1. Database Schema Specifications

#### Table 1: `analytics_snapshots`
Stores pre-aggregated daily/weekly metric summaries to optimize dashboard loading times and avoid running heavy calculation queries on live transactional records.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `branch_id` | `CHAR(36)` | FOREIGN KEY, NULLABLE | Mapped branch boundaries; null for global aggregates. |
| `snapshot_date` | `DATE` | NOT NULL | Target date of summary calculations. |
| `metric_key` | `VARCHAR(50)` | NOT NULL | Metric code (e.g. `gross_revenue_cents`, `occupancy_rate`). |
| `metric_value` | `DECIMAL(15,4)` | NOT NULL | Calculated numeric total. |
| `created_at` | `TIMESTAMP` | NULL | Creation timestamp. |

* **Foreign Keys**:
  * `branch_id` references `branches(id)` on delete cascade.
* **Indexes**:
  * Unique Composite Index on `(branch_id, snapshot_date, metric_key)`.

---

#### Table 2: `analytics_cache`
Stores cached query data chunks for dashboard charts.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `key` | `VARCHAR(255)` | PRIMARY KEY | Unique cache key. |
| `value` | `MEDIUMTEXT` | NOT NULL | JSON string payload. |
| `expiration` | `INT` | NOT NULL | Expiry epoch timestamp. |

---

#### Table 3: `analytics_reports`
Saved settings configurations for custom reports.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Mapped creator user ID. |
| `report_type` | `VARCHAR(30)` | NOT NULL | Type (`revenue`, `occupancy`, `conversions`). |
| `parameters` | `JSON` | NOT NULL | JSON parameters filters (dates, media type list). |
| `created_at` | `TIMESTAMP` | NULL | Creation timestamp. |

---

#### Table 4: `analytics_exports`
Logs downloads of reports to track access.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | User who initiated export. |
| `export_type` | `VARCHAR(10)` | NOT NULL | Format (`PDF`, `XLSX`, `CSV`). |
| `filters_used` | `JSON` | NOT NULL | JSON parameters logged. |
| `downloaded_at` | `TIMESTAMP` | NOT NULL | Download timestamp. |

---

#### Table 5: `dashboard_widgets`
Layout preference records for customizable portals.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | User config owner. |
| `widget_key` | `VARCHAR(50)` | NOT NULL | Widget identifier. |
| `position` | `INT` | NOT NULL | Layout sort order coordinates. |

---

#### Table 6: `report_templates`
Pre-defined layouts matching default reports.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `name` | `VARCHAR(100)` | NOT NULL | Template name. |
| `layout` | `JSON` | NOT NULL | Layout structural columns definition data. |

---

#### Table 7: `scheduled_reports`
Automated reports setup for recurring email dispatches.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | User setup manager. |
| `report_template_id`| `CHAR(36)` | FOREIGN KEY, NOT NULL | Mapped template. |
| `frequency` | `VARCHAR(10)` | NOT NULL | Frequency (`daily`, `weekly`, `monthly`). |
| `recipient_email` | `VARCHAR(100)` | NOT NULL | Destination email address. |
| `is_active` | `TINYINT(1)` | NOT NULL, DEFAULT 1 | Active state. |

---

## Future Scope

* **Data Warehouse Syncing**: Scripts copying snapshots to external cloud data lakes in V2.
