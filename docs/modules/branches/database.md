# Branch Module: Database Design

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the relational database schema structure, relationships, indexes, and constraints for the Branch module of SODARS.

---

## Scope

This document specifies the schema layout of three tables: `branches`, `branch_users`, and `branch_coverage_cities`. It defines the technical standards for primary key types, auditing timestamps, soft deletes, and foreign key relations.

---

## Business Rules

### 1. Database Schema Specifications

#### Table 1: `branches`
Stores the details of each regional operational business unit.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `name` | `VARCHAR(100)` | UNIQUE, NOT NULL | Full name of the branch (e.g., "Branch India North"). |
| `code` | `VARCHAR(10)` | UNIQUE, NOT NULL | Unique short code (e.g., "DELHI", "MUMBAI"). |
| `timezone` | `VARCHAR(50)` | NOT NULL | Target timezone offset code (e.g., "Asia/Kolkata"). |
| `currency_code` | `VARCHAR(3)` | NOT NULL, DEFAULT "INR" | Local currency representation. |
| `markup_percentage` | `INT` | NOT NULL, DEFAULT 20 | Markup percentage applied to screen Net Prices (max 20). |
| `support_email` | `VARCHAR(100)` | NOT NULL | Regional branch customer support email address. |
| `support_phone` | `VARCHAR(20)` | NOT NULL | Regional branch customer support contact number. |
| `is_active` | `TINYINT(1)` | NOT NULL, DEFAULT 1 | Activation flag (1 = Active, 0 = Inactive). |
| `created_at` | `TIMESTAMP` | NULL | Database auto-insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Database auto-update timestamp. |
| `deleted_at` | `TIMESTAMP` | NULL | Soft delete timestamp. Null if active. |

* **Indexes**:
  * Index on `code` (fast query lookups).
  * Index on `is_active` (filtering marketplace display states).

---

#### Table 2: `branch_users`
Associates system users (managers and regional staff) with their managed branch.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `branch_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Reference to `branches.id`. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Reference to global `users.id`. |
| `role` | `VARCHAR(20)` | NOT NULL | Staff classification (`branch_manager`, `branch_staff`). |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `branch_id` references `branches(id)` on delete restrict.
  * `user_id` references `users(id)` on delete cascade.
* **Indexes**:
  * Unique Composite Index on `(branch_id, user_id, role)`.

---

#### Table 3: `branch_coverage_cities`
Stores the geographic city bounds managed under each branch's coverage area.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `branch_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Reference to `branches.id`. |
| `city_name` | `VARCHAR(100)` | NOT NULL | Name of the coverage city (e.g., "Noida"). |
| `state_name` | `VARCHAR(100)` | NOT NULL | State/Province name (e.g., "Uttar Pradesh"). |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `branch_id` references `branches(id)` on delete cascade.
* **Indexes**:
  * Unique Composite Index on `(branch_id, city_name)` to prevent duplicate city allocations.

---

## Future Scope

* **Table `branch_targets`**: Deferred for V2 to track monthly sales goals.
* **Table `branch_expenses`**: Deferred for V2 to log physical site maintenance expenses.
