# 07. Database Guidelines

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define global database guidelines, schema standards, primary/foreign key approaches, and financial record-keeping standards for SODARS.

---

## Scope

This document establishes the relational rules that all database tables must comply with. Table schemas must not be declared in this file; they must reside inside their respective module directories (e.g., `modules/inventory/database.md`).

---

## Business Rules

### 1. Database Naming Conventions
* **Table Names**: Must use plural `snake_case` (e.g., `providers`, `digital_assets`, `campaign_slots`).
* **Column Names**: Must use singular `snake_case` (e.g., `first_name`, `operating_status`).
* **Pivot Tables**: Alpha-sorted singular names joined by an underscore (e.g., `campaign_digital_asset`).

### 2. Primary & Foreign Keys
* **UUID Strategy**: To prevent sequential ID exposure and scraping, all primary keys must use UUID (v4) character columns.
  * Columns type: `CHAR(36)` named `id`.
  * Do not expose integer auto-increment values in public endpoints.
* **Foreign Key Naming**: `singular_parent_table_id` (e.g., `branch_id`, `provider_id`).
* **Constraints**: Hard foreign key database constraints must be created in migrations. Nullable foreign keys are permitted where optional relationships exist, but cascades must be defined explicitly (e.g., `ON DELETE RESTRICT` for financial logs).

### 3. Soft Deletes & Audit Trails
* **Soft Deletes**: Active inventory, bookings, branches, and profiles must never be permanently deleted from the database.
  * Columns must include `deleted_at` timestamp.
  * Deleted items must be hidden from normal query scopes by default.
* **Standard Timestamps**: Every table must contain `created_at` and `updated_at` columns.

### 4. Monetary Representation
* **Float Prevention**: Do not use `float` or `double` data types for pricing to avoid rounding errors.
* **Cents/Paisa Storage**: Store all financial figures, pricing, and markup margins as **integers representing the lowest currency unit (cents or paisa)**.
  * Column naming convention must end with `_cents` (e.g., `net_price_cents`, `retail_price_cents`, `markup_amount_cents`).
  * Example:
    * Net Price: **₹1,500.00** -> Database Value: **150000**
    * Marketplace Retail Price: **₹1,800.00** -> Database Value: **180000**

### 5. Multi-Branch Division
* Almost all transactional data (inventories, bookings, users) must contain a `branch_id` foreign key.
* The backend API must automatically append a `where('branch_id', $user->branch_id)` constraint for non-admin tokens.

---

## Future Scope

* Partitioning tables (like large log tables or player proof-of-performance tables) by year/month to optimize query speeds.
* Introduction of read-replicas in regional cloud areas for database scalability.
