# Mobile Module: Database Design

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the database schemas, tables, constraints, and cleanup policies for the Mobile app registry of SODARS.

---

## Scope

This document specifies ten mobile-specific tables: `mobile_devices`, `mobile_sessions`, `device_tokens`, `offline_sync_queue`, `offline_changes`, `offline_media`, `offline_logs`, `app_versions`, `crash_reports`, and `location_tracking`.

---

## Business Rules

### 1. Database Schema Specifications

#### Table 1: `mobile_devices`
Tracks physical mobile devices registered on the platform.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `users.id`. |
| `device_uuid` | `VARCHAR(100)` | UNIQUE, NOT NULL | Hardware device UUID key. |
| `os_platform` | `VARCHAR(20)` | NOT NULL | OS Platform (`ios`, `android`). |
| `os_version` | `VARCHAR(20)` | NOT NULL | OS release version number (e.g. `15.4.1`). |
| `app_version` | `VARCHAR(20)` | NOT NULL | Active app build version (e.g. `1.0.2`). |
| `created_at` | `TIMESTAMP` | NULL | Registration timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

---

#### Table 2: `mobile_sessions`
Active secure tokens mapping.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `device_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `mobile_devices.id`. |
| `refresh_token` | `VARCHAR(255)` | UNIQUE, NOT NULL | Refresh token string. |
| `expires_at` | `TIMESTAMP` | NOT NULL | Expiry threshold timestamp. |
| `created_at` | `TIMESTAMP` | NULL | Creation timestamp. |

---

#### Table 3: `offline_sync_queue`
Local queue registry tracking pending synchronizations.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `device_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `mobile_devices.id`. |
| `payload` | `JSON` | NOT NULL | JSON request payload details (e.g. updated GPS points). |
| `action_type` | `VARCHAR(50)` | NOT NULL | Sync type (e.g. `update_gps`, `upload_proof`). |
| `attempts` | `INT` | NOT NULL, DEFAULT 0 | Count of sync retry executions. |
| `status` | `VARCHAR(20)` | NOT NULL, DEFAULT "pending" | State (`pending`, `syncing`, `completed`, `failed`). |
| `created_at` | `TIMESTAMP` | NULL | Log timestamp. |

---

#### Table 4: `crash_reports`
App error diagnostics metrics.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `device_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `mobile_devices.id`. |
| `error_stack` | `TEXT` | NOT NULL | Javascript stack trace string. |
| `device_state` | `JSON` | NOT NULL | Device state snapshot (battery, network type). |
| `created_at` | `TIMESTAMP` | NOT NULL | Log timestamp. |

---

## Retention & Cleanup Policies
* **Crash Reports and Device Logs**: Retention limit of **60 days**. A monthly cron worker deletes expired crash entries to optimize database size.
* **Offline Sync Logs**: Completed queue logs (`status = completed`) are deleted from the database **7 days** after successful synchronization.
* **Device Sessions**: Expired device sessions are deleted automatically during JWT check cycles.
