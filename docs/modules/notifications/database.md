# Notifications Module: Database Design

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the relational database schemas, columns, constraints, foreign key mappings, and indexes for the Notifications module of SODARS.

---

## Scope

This document specifies eight tables: `notifications`, `notification_templates`, `notification_logs`, `notification_queue`, `notification_preferences`, `notification_devices`, `notification_failures`, and `notification_variables`.

---

## Business Rules

### 1. Database Schema Specifications

#### Table 1: `notifications`
Individual communication dispatch records.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `recipient_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Target global user mapping. |
| `title` | `VARCHAR(150)` | NOT NULL | Title / Subject line text. |
| `body` | `TEXT` | NOT NULL | Rendered message text (html or string). |
| `channel` | `VARCHAR(30)` | NOT NULL | Channel (`email`, `sms`, `whatsapp`, `push`, `in_app`). |
| `status` | `VARCHAR(20)` | NOT NULL, DEFAULT "pending" | State (`pending`, `queued`, `processing`, `delivered`, `failed`, `read`, `archived`). |
| `created_at` | `TIMESTAMP` | NULL | Creation timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `recipient_id` references `users(id)` on delete cascade.
* **Indexes**:
  * Index on `status`, `channel`.

---

#### Table 2: `notification_templates`
Pre-defined layouts used by the system router.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `event_key` | `VARCHAR(100)` | NOT NULL | System event identifier (e.g. `booking_submitted`). |
| `channel` | `VARCHAR(30)` | NOT NULL | Target delivery channel (e.g., `email`). |
| `subject_template` | `VARCHAR(255)` | NULL | Subject template string with variables. |
| `body_template` | `TEXT` | NOT NULL | Main message body with variable templates. |
| `is_active` | `TINYINT(1)` | NOT NULL, DEFAULT 1 | Active state switch. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Indexes**:
  * Unique Composite Index on `(event_key, channel)`.

---

#### Table 3: `notification_logs`
Logs response metadata from external APIs (SendGrid, Twilio, FCM).

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `notification_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `notifications.id`. |
| `sent_at` | `TIMESTAMP` | NOT NULL | Dispatch timestamp. |
| `response_payload` | `TEXT` | NULL | JSON raw payload returned by the gateway. |

* **Foreign Keys**:
  * `notification_id` references `notifications(id)` on delete cascade.

---

#### Table 4: `notification_queue`
Manages asynchronous queue dispatching and schedules retries.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `notification_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `notifications.id`. |
| `attempts` | `INT` | NOT NULL, DEFAULT 0 | Count of delivery attempts executed. |
| `next_attempt_at` | `TIMESTAMP` | NOT NULL | Scheduled timestamp for next retry. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |

---

#### Table 5: `notification_preferences`
Tracks user channel opt-in/opt-out preferences.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `users.id`. |
| `event_key` | `VARCHAR(100)` | NOT NULL | Target system event. |
| `channel` | `VARCHAR(30)` | NOT NULL | Target delivery channel. |
| `is_enabled` | `TINYINT(1)` | NOT NULL, DEFAULT 1 | 1 = Enabled, 0 = Disabled. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

---

#### Table 6: `notification_devices`
Stores FCM device tokens for mobile push notifications.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `users.id`. |
| `device_token` | `VARCHAR(255)` | UNIQUE, NOT NULL | FCM/APNs token string. |
| `device_type` | `VARCHAR(10)` | NOT NULL | OS (`ios`, `android`). |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

---

#### Table 7: `notification_failures`
Records detailed errors of failed dispatches.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `notification_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `notifications.id`. |
| `error_message` | `TEXT` | NOT NULL | System error string stack trace. |
| `created_at` | `TIMESTAMP` | NOT NULL | Timestamp of failure. |

---

#### Table 8: `notification_variables`
Tracks supported templates variables. This is a configuration reference table.

| Variable Name | Description |
| :--- | :--- |
| `{{customer_name}}` | Name of the advertiser company/individual. |
| `{{provider_name}}` | Name of the screen owner. |
| `{{booking_number}}` | Unique ID/Ref number of the transaction booking. |
| `{{campaign_name}}` | Active name of the ad campaign flight. |
| `{{inventory_name}}` | Display name of the screen asset. |
| `{{branch_name}}` | Name of the managing branch. |
| `{{amount}}` | Payout or booking cost figure. |
| `{{payment_reference}}` | Offline bank reference code. |
| `{{date}}` | Date timestamp log. |
| `{{company_name}}` | Central system corporate title. |
