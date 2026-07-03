# Campaign Module: Database Design

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the relational database schemas, columns, constraints, foreign key mappings, and indexes for the Campaign module of SODARS.

---

## Scope

This document specifies seven tables: `campaigns`, `campaign_inventory`, `campaign_creatives`, `campaign_schedule`, `campaign_proofs`, `campaign_notes`, and `campaign_logs`.

---

## Business Rules

### 1. Database Schema Specifications

#### Table 1: `campaigns`
Stores the central campaign log.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `booking_id` | `CHAR(36)` | FOREIGN KEY, UNIQUE, NOT NULL | Link to the approved commercial `bookings.id`. |
| `customer_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to global `customers.id`. |
| `branch_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to regional managing `branches.id`. |
| `name` | `VARCHAR(150)` | NOT NULL | Campaign name. |
| `start_date` | `DATE` | NOT NULL | Flight start date. |
| `end_date` | `DATE` | NOT NULL | Flight end date. |
| `status` | `VARCHAR(30)` | NOT NULL, DEFAULT "draft" | Status (`draft`, `artwork_pending`, `scheduled`, `running`, `paused`, `completed`, `archived`). |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |
| `deleted_at` | `TIMESTAMP` | NULL | Soft delete support. |

* **Foreign Keys**:
  * `booking_id` references `bookings(id)` on delete restrict.
  * `customer_id` references `customers(id)` on delete restrict.
  * `branch_id` references `branches(id)` on delete restrict.

---

#### Table 2: `campaign_inventory`
Pivot table mapping campaigns to target displays.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `campaign_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `campaigns.id`. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |

* **Foreign Keys**:
  * `campaign_id` references `campaigns(id)` on delete cascade.
  * `inventory_id` references `inventories(id)` on delete restrict.

---

#### Table 3: `campaign_creatives`
Stores files uploaded by the Customer for display playback.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `campaign_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `campaigns.id`. |
| `file_name` | `VARCHAR(150)` | NOT NULL | Original uploaded filename. |
| `file_path` | `VARCHAR(255)` | NOT NULL | S3 storage identifier key. |
| `file_type` | `VARCHAR(30)` | NOT NULL | Format (`JPG`, `PNG`, `PDF`, `AI`, `PSD`, `CDR`, `ZIP`). |
| `status` | `VARCHAR(20)` | NOT NULL, DEFAULT "pending" | Audit state (`pending`, `approved`, `rejected`). |
| `rejection_reason` | `TEXT` | NULL | Auditor feedback text if rejected. |
| `uploaded_at` | `TIMESTAMP` | NOT NULL | Upload timestamp. |

---

#### Table 4: `campaign_schedule`
Calendar grid mapping loop slot indexes.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `campaign_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `campaigns.id`. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |
| `date` | `DATE` | NOT NULL | Active date. |
| `slot_index` | `INT` | NOT NULL | Mapped display loop index (e.g. 1 to 6). |

* **Indexes**:
  * Unique Composite Index on `(inventory_id, date, slot_index)`.

---

#### Table 5: `campaign_proofs`
Auditing proofs uploaded by the Provider confirming execution.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `campaign_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `campaigns.id`. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |
| `file_path` | `VARCHAR(255)` | NOT NULL | S3 verification file path. |
| `notes` | `TEXT` | NULL | Execution notes. |
| `uploaded_by` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Staff user ID who uploaded files. |
| `status` | `VARCHAR(20)` | NOT NULL, DEFAULT "pending" | Verification state (`pending`, `verified`, `rejected`). |
| `verified_by` | `CHAR(36)` | FOREIGN KEY, NULLABLE | Branch manager ID who verified proofs. |
| `uploaded_at` | `TIMESTAMP` | NOT NULL | Upload timestamp. |

---

## Future Scope

* **Table `campaign_telemetry_logs`**: Play logs sent by IoT software nodes (deferred to V2).
