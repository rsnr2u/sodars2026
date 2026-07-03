# Settings Module: Database Design

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the database schema, tables, columns, indexes, and caching strategies for the Settings module of SODARS.

---

## Scope

This document specifies the database schemas used to store, secure, and cache system configurations. It details parameters for tables including settings repositories, metadata profiles, audit logs, and feature flag maps.

---

## Business Rules

### 1. Database Schema Specifications

#### Table 1: `system_settings`
Stores unified key-value configuration pairs. To avoid database bloat, individual category settings (SMTP, SMS, WhatsApp, S3, Maps, security) are mapped inside this table using categories and groups.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `setting_key` | `VARCHAR(100)` | UNIQUE, NOT NULL | Unique system key (e.g. `smtp_host`, `google_maps_api_key`). |
| `setting_value` | `TEXT` | NULL | Value of config. Sensitive secrets are saved as encrypted strings. |
| `group_name` | `VARCHAR(50)` | NOT NULL | Setting group (`company`, `marketplace`, `pricing`, `api_secrets`). |
| `category` | `VARCHAR(50)` | NOT NULL | Subcategory (`email`, `sms`, `whatsapp`, `storage`, `maps`). |
| `is_encrypted` | `TINYINT(1)` | NOT NULL, DEFAULT 0 | 1 = Value is encrypted via AES-256 on write, 0 = Plaintext. |
| `is_env_override`| `TINYINT(1)` | NOT NULL, DEFAULT 0 | 1 = Value is overridden by system `.env` parameters. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Indexes**:
  * Unique index on `setting_key`.
  * Index on `(group_name, category)`.

---

#### Table 2: `company_profiles`
Stores official company details for branding and legal invoices.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `legal_name` | `VARCHAR(150)` | NOT NULL | Official company name. |
| `tax_number` | `VARCHAR(50)` | NOT NULL | Corporate tax ID (GSTIN / VAT number). |
| `address_line_1` | `VARCHAR(255)` | NOT NULL | Corporate street details. |
| `city` | `VARCHAR(100)` | NOT NULL | Headquarters city. |
| `state` | `VARCHAR(100)` | NOT NULL | Headquarters state. |
| `zip_code` | `VARCHAR(20)` | NOT NULL | Postal code. |
| `logo_s3_path` | `VARCHAR(255)` | NULL | S3 file path key of corporate logo graphic. |
| `primary_color` | `VARCHAR(7)` | NOT NULL, DEFAULT "#1E3A8A"| Branding theme primary hex color code. |
| `secondary_color`| `VARCHAR(7)` | NOT NULL, DEFAULT "#10B981"| Branding theme secondary hex color code. |

---

#### Table 3: `feature_flags`
Controls modular screen/feature activation dynamically.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `flag_key` | `VARCHAR(100)` | UNIQUE, NOT NULL | Flag key identifier (e.g. `enable_whatsapp_alerts`). |
| `is_enabled` | `TINYINT(1)` | NOT NULL, DEFAULT 1 | 1 = Feature active, 0 = Deactivated. |
| `description` | `VARCHAR(255)` | NULL | Explains what the flag controls. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

---

#### Table 4: `system_logs` (Audit Trails)
Tracks setting configuration adjustments.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Modifier user ID. |
| `action` | `VARCHAR(100)` | NOT NULL | Action log (e.g. `update_smtp_config`). |
| `old_value` | `JSON` | NULL | Snapshot of settings details before change. |
| `new_value` | `JSON` | NULL | Snapshot of settings details after change. |
| `ip_address` | `VARCHAR(45)` | NOT NULL | IP coordinate address of modifier user. |
| `created_at` | `TIMESTAMP` | NOT NULL | Timestamp of log. |

---

#### Table 5: `system_backups`
Registry logs of database backup archives.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `file_name` | `VARCHAR(150)` | NOT NULL | Backup archive file name. |
| `file_path` | `VARCHAR(255)` | NOT NULL | S3 storage key. |
| `file_size_bytes`| `BIGINT` | NOT NULL | Size of archive files. |
| `created_at` | `TIMESTAMP` | NOT NULL | Generation timestamp. |

---

#### Table 6: `system_versions`
Release version mapping records.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `version_tag` | `VARCHAR(20)` | UNIQUE, NOT NULL | Release version (e.g., `v1.0.0`). |
| `release_notes` | `TEXT` | NULL | Markdown version notes. |
| `deployed_at` | `TIMESTAMP` | NOT NULL | Deployment timestamp. |

---

## Caching Strategy
* To prevent bottlenecking database select requests on every page route load, all system configurations must be cached in memory (Redis / Memcached).
* Configuration models must implement custom listeners that flush the setting cache key instantly whenever a database write/update event executes.
