# Provider Module: Database Design

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the relational database schemas, columns, constraints, foreign key mappings, and indexes for the Provider module of SODARS.

---

## Scope

This document specifies seven tables: `providers`, `provider_contacts`, `provider_documents`, `provider_staff`, `provider_subscriptions`, `provider_bank_accounts`, and `provider_settings`.

---

## Business Rules

### 1. Database Schema Specifications

#### Table 1: `providers`
Stores the corporate profile of the screen owner.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `company_name` | `VARCHAR(150)` | NOT NULL | Registered name of the company/entity. |
| `registration_number` | `VARCHAR(50)` | UNIQUE, NOT NULL | Corporate tax ID or business registry number. |
| `default_branch_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | The default branch responsible for the account. |
| `status` | `VARCHAR(20)` | NOT NULL, DEFAULT "draft" | Status (`draft`, `pending`, `verified`, `suspended`, `deactivated`). |
| `created_at` | `TIMESTAMP` | NULL | Creation timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |
| `deleted_at` | `TIMESTAMP` | NULL | Soft delete support. |

* **Foreign Keys**:
  * `default_branch_id` references `branches(id)` on delete restrict.
* **Indexes**:
  * Index on `status`.
  * Index on `registration_number`.

---

#### Table 2: `provider_contacts`
Primary contact information for business communications.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `provider_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `providers.id`. |
| `contact_name` | `VARCHAR(100)` | NOT NULL | Name of primary communications representative. |
| `email` | `VARCHAR(100)` | UNIQUE, NOT NULL | Primary email address. |
| `phone` | `VARCHAR(20)` | NOT NULL | Primary mobile/business contact number. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `provider_id` references `providers(id)` on delete cascade.

---

#### Table 3: `provider_documents`
Auditing credentials uploaded for compliance verification.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string. |
| `provider_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `providers.id`. |
| `document_type` | `VARCHAR(30)` | NOT NULL | Type (`tax_certificate`, `business_registry`, `screen_ownership_proof`). |
| `file_path` | `VARCHAR(255)` | NOT NULL | S3 storage identifier key. |
| `status` | `VARCHAR(20)` | NOT NULL, DEFAULT "pending" | Status (`pending`, `approved`, `rejected`). |
| `comment` | `TEXT` | NULL | Reasons for rejection, provided by auditor. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `provider_id` references `providers(id)` on delete cascade.

---

#### Table 4: `provider_staff`
Maps users (credentials/passwords) to provider accounts.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string. |
| `provider_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `providers.id`. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to global `users.id`. |
| `role` | `VARCHAR(20)` | NOT NULL | Role designation (`provider_admin`, `provider_staff`). |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `provider_id` references `providers(id)` on delete cascade.
  * `user_id` references `users(id)` on delete cascade.
* **Indexes**:
  * Unique Composite Index on `(provider_id, user_id)`.

---

#### Table 5: `provider_subscriptions`
Active tier settings governing display listing counts.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string. |
| `provider_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `providers.id`. |
| `tier_name` | `VARCHAR(30)` | NOT NULL | Subscription code (`free`, `standard`, `unlimited`). |
| `max_active_screens` | `INT` | NOT NULL | Maximum screen allocation allowed for active lists. |
| `starts_at` | `TIMESTAMP` | NOT NULL | Subscription begin timestamp. |
| `ends_at` | `TIMESTAMP` | NOT NULL | Subscription termination limit. |
| `is_active` | `TINYINT(1)` | NOT NULL, DEFAULT 1 | Active state flag (1 = Active, 0 = Expired). |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `provider_id` references `providers(id)` on delete cascade.

---

#### Table 6: `provider_bank_accounts`
Payout billing profiles for verified bank transfers.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string. |
| `provider_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `providers.id`. |
| `bank_name` | `VARCHAR(100)` | NOT NULL | Target bank organization title. |
| `account_holder` | `VARCHAR(150)` | NOT NULL | Registered name of account holder. |
| `account_number` | `VARCHAR(50)` | NOT NULL | Bank routing account number. |
| `routing_code` | `VARCHAR(30)` | NOT NULL | SWIFT, IFSC, or routing token. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `provider_id` references `providers(id)` on delete cascade.

---

#### Table 7: `provider_settings`
Custom settings for the provider dashboard and emails.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string. |
| `provider_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `providers.id`. |
| `enable_marketplace` | `TINYINT(1)` | NOT NULL, DEFAULT 1 | Flag to show/hide all listings from public map search. |
| `notify_email_bookings` | `TINYINT(1)` | NOT NULL, DEFAULT 1 | Toggle to dispatch email alerts on slot purchases. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `provider_id` references `providers(id)` on delete cascade.

---

## Future Scope

* **Table `provider_ratings`**: Out of scope for V1. Records SLA verification statistics and ratings.
