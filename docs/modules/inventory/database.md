# Inventory Module: Database Design

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the relational database schemas, columns, constraints, foreign key mappings, and indexes for the Inventory module of SODARS.

---

## Scope

This document specifies eight tables: `inventories`, `inventory_media`, `inventory_documents`, `inventory_rates`, `inventory_availability`, `inventory_categories`, `inventory_tags`, and `inventory_audit_logs`.

---

## Business Rules

### 1. Database Schema Specifications

#### Table 1: `inventories`
Stores details of listed digital/static assets.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `provider_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `providers.id`. |
| `branch_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to managing `branches.id`. |
| `name` | `VARCHAR(150)` | NOT NULL | Display name of the screen/billboard. |
| `media_type` | `VARCHAR(50)` | NOT NULL | Type (`billboard`, `hoarding`, `led_screen`, etc.). |
| `latitude` | `DECIMAL(10,8)` | NOT NULL | Precise GPS Latitude. |
| `longitude` | `DECIMAL(11,8)` | NOT NULL | Precise GPS Longitude. |
| `address` | `VARCHAR(255)` | NOT NULL | Physical street location address. |
| `city` | `VARCHAR(100)` | NOT NULL | City name (e.g., "Noida"). |
| `state` | `VARCHAR(100)` | NOT NULL | State name (e.g., "Uttar Pradesh"). |
| `area` | `VARCHAR(100)` | NOT NULL | Sub-city area or neighborhood. |
| `width_cm` | `INT` | NOT NULL | Physical asset width in centimeters. |
| `height_cm` | `INT` | NOT NULL | Physical asset height in centimeters. |
| `orientation` | `VARCHAR(15)` | NOT NULL | Layout (`portrait`, `landscape`). |
| `illuminated` | `TINYINT(1)` | NOT NULL, DEFAULT 0 | Has backlight/illumination (1 = Yes, 0 = No). |
| `net_price_cents` | `INT` | NOT NULL | Daily Net Price specified by the Provider in cents/paisa. |
| `status` | `VARCHAR(20)` | NOT NULL, DEFAULT "draft" | Status (`draft`, `pending`, `approved`, `suspended`, `archived`). |
| `enable_marketplace` | `TINYINT(1)` | NOT NULL, DEFAULT 1 | Marketplace display toggle. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |
| `deleted_at` | `TIMESTAMP` | NULL | Soft delete support. |

* **Foreign Keys**:
  * `provider_id` references `providers(id)` on delete restrict.
  * `branch_id` references `branches(id)` on delete restrict.
* **Indexes**:
  * Spatial Index or standard compound index on `(latitude, longitude)`.
  * Index on `status`, `city`, `media_type`.

---

#### Table 2: `inventory_media`
Stores photographs or videos of the physical site.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |
| `media_type` | `VARCHAR(20)` | NOT NULL | Class (`photo`, `video`). |
| `file_path` | `VARCHAR(255)` | NOT NULL | S3 storage key. |
| `is_primary` | `TINYINT(1)` | NOT NULL, DEFAULT 0 | Is the cover photo on search cards. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

---

#### Table 3: `inventory_documents`
Permits, structural certificates, or regulatory documents.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |
| `document_type` | `VARCHAR(50)` | NOT NULL | Type (`permit_license`, `ownership_certificate`). |
| `file_path` | `VARCHAR(255)` | NOT NULL | S3 file key. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |

---

#### Table 4: `inventory_rates`
Manages custom pricing rate periods (e.g., festival pricing, off-peak).

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |
| `rate_cents` | `INT` | NOT NULL | Custom daily rate in cents/paisa. |
| `start_date` | `DATE` | NOT NULL | Start of custom price period. |
| `end_date` | `DATE` | NOT NULL | End of custom price period. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |

---

#### Table 5: `inventory_availability`
Tracks booked slot capacity per calendar day.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |
| `date` | `DATE` | NOT NULL | Calendar date. |
| `slots_booked` | `INT` | NOT NULL, DEFAULT 0 | Count of slots booked for this day (max capacity e.g., 6). |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |

* **Indexes**:
  * Unique Composite Index on `(inventory_id, date)`.

---

#### Table 6: `inventory_categories`
Labels categorizing inventory assets (e.g., Premium, Commercial).

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `name` | `VARCHAR(50)` | UNIQUE, NOT NULL | Category name. |

---

#### Table 7: `inventory_tags`
Search tags applied to inventory (e.g., "Airport", "Main Market").

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |
| `tag_name` | `VARCHAR(50)` | NOT NULL | Tag label. |

---

#### Table 8: `inventory_audit_logs`
Tracks settings changes (status updates, pricing adjustments).

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Mapped modifier user. |
| `action` | `VARCHAR(50)` | NOT NULL | Action key (`status_change`, `rate_change`). |
| `old_values` | `JSON` | NULL | Previous settings data. |
| `new_values` | `JSON` | NULL | Updated settings data. |
| `created_at` | `TIMESTAMP` | NOT NULL | Change timestamp. |

---

## Future Scope

* **Table `inventory_telemetry_heartbeats`**: Tracks physical screen ping loops in V2.
