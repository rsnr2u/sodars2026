# Shared Module: Database Design

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the database schemas, tables, constraints, foreign key mappings, and indexes for the Shared infrastructure module of SODARS.

---

## Scope

This document specifies the database schemas used to manage media assets, audit trails, geographic masters, and import/export logs.

---

## Business Rules

### 1. Database Schema Specifications

#### Table 1: `media_library` (Uploaded Files)
Central registry tracking all assets uploaded to AWS S3.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `file_name` | `VARCHAR(150)` | NOT NULL | Original file name. |
| `file_path` | `VARCHAR(255)` | NOT NULL | S3 storage key locator. |
| `mime_type` | `VARCHAR(50)` | NOT NULL | File type format. |
| `file_size_bytes`| `BIGINT` | NOT NULL | Size in bytes. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | ID of user who executed upload. |
| `created_at` | `TIMESTAMP` | NULL | Upload timestamp. |

---

#### Table 2: `audit_logs`
System-wide change audit logger tracking column updates.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NULLABLE | User who performed action (null for guest errors). |
| `event` | `VARCHAR(50)` | NOT NULL | Action key (e.g. `update`, `delete`). |
| `auditable_type`| `VARCHAR(100)` | NOT NULL | Mapped Model Class (e.g., `App\Models\Inventory`). |
| `auditable_id`  | `CHAR(36)` | NOT NULL | Mapped record primary key UUID. |
| `old_values` | `JSON` | NULL | Old row database state JSON. |
| `new_values` | `JSON` | NULL | Updated row database state JSON. |
| `created_at` | `TIMESTAMP` | NOT NULL | Action log timestamp. |

---

#### Table 3: `activity_logs`
Tracks simple user behaviors (e.g. log in attempts, search page hits).

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `users.id`. |
| `log_message` | `VARCHAR(255)` | NOT NULL | Text description of action. |
| `ip_address` | `VARCHAR(45)` | NOT NULL | IPv4 / IPv6 coordinates. |
| `created_at` | `TIMESTAMP` | NOT NULL | Creation timestamp. |

---

#### Tables 4 to 8: Master Geographies
Fixed hierarchy mapping regional constraints.

* **Table 4: `countries`**:
  * `id`: `CHAR(36)` Primary Key.
  * `name`: `VARCHAR(100)` UNIQUE, NOT NULL.
  * `iso_code`: `VARCHAR(3)` UNIQUE, NOT NULL (e.g. `IND`, `USA`).
* **Table 5: `states`**:
  * `id`: `CHAR(36)` Primary Key.
  * `country_id`: `CHAR(36)` Foreign Key referencing `countries(id)` on delete cascade.
  * `name`: `VARCHAR(100)` NOT NULL.
* **Table 6: `districts`**:
  * `id`: `CHAR(36)` Primary Key.
  * `state_id`: `CHAR(36)` Foreign Key referencing `states(id)` on delete cascade.
  * `name`: `VARCHAR(100)` NOT NULL.
* **Table 7: `cities`**:
  * `id`: `CHAR(36)` Primary Key.
  * `district_id`: `CHAR(36)` Foreign Key referencing `districts(id)` on delete cascade.
  * `name`: `VARCHAR(100)` NOT NULL.
* **Table 8: `pincodes`**:
  * `id`: `CHAR(36)` Primary Key.
  * `city_id`: `CHAR(36)` Foreign Key referencing `cities(id)` on delete cascade.
  * `code`: `VARCHAR(10)` NOT NULL (Postal index code).

---

#### Table 9: `system_sequences`
Registry database sequence numbers. Used to generate human-readable numeric billing codes.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `name` | `VARCHAR(50)` | UNIQUE, NOT NULL | Sequence code (e.g. `booking_invoice_seq`). |
| `current_value` | `BIGINT` | NOT NULL, DEFAULT 1 | Current index value. |

---

#### Table 10: `temporary_files`
Tracks S3 objects pending deletion (e.g., CSV imports, raw crops uploads).

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `file_path` | `VARCHAR(255)` | NOT NULL | S3 storage key locator. |
| `expiry_at` | `TIMESTAMP` | NOT NULL | Expiry threshold timestamp. |
| `created_at` | `TIMESTAMP` | NOT NULL | Generation timestamp. |

---

## Retention & Cleanup Rules
* **Audit and Activity logs**: Retention period of **7 years** (mandatory for corporate accounting audits). Purging requires manual DB script triggers.
* **Temporary files cleanup**: A daily server cron task runs at 03:00 AM, scanning `temporary_files` where `expiry_at < NOW()`, deletes files from the S3 bucket, and removes database rows.
