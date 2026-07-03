# Marketplace Module: Database Design

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the relational database schemas, columns, constraints, foreign key mappings, and indexes for the Marketplace module of SODARS.

---

## Scope

This document specifies six tables: `marketplace_settings`, `marketplace_featured_inventory`, `marketplace_search_logs`, `marketplace_saved_searches`, `marketplace_favorites`, and `marketplace_recent_views`. Core inventory and pricing columns are not duplicated here.

---

## Business Rules

### 1. Database Schema Specifications

#### Table 1: `marketplace_settings`
Global configuration settings (e.g. system banner text, homepage layout settings).

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `key` | `VARCHAR(50)` | UNIQUE, NOT NULL | Setting identifier (e.g. `promo_banner_active`). |
| `value` | `TEXT` | NULL | Dynamic configuration text/JSON payload. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

---

#### Table 2: `marketplace_featured_inventory`
Displays select screens at top list positions for promotional actions.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |
| `sort_order` | `INT` | NOT NULL, DEFAULT 0 | Ordering index for visual grids. |
| `starts_at` | `TIMESTAMP` | NOT NULL | Promo window start. |
| `ends_at` | `TIMESTAMP` | NOT NULL | Promo window expiration. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `inventory_id` references `inventories(id)` on delete cascade.

---

#### Table 3: `marketplace_search_logs`
Tracks query patterns to optimize search filters.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, NULLABLE | User link if logged in, null for guests. |
| `query_text` | `VARCHAR(255)` | NULL | Text searches typed into the location search bar. |
| `filters_applied` | `JSON` | NULL | JSON dump of filter parameters (price ranges, categories). |
| `created_at` | `TIMESTAMP` | NOT NULL | Timestamp of search execution. |

---

#### Table 4: `marketplace_saved_searches`
Allows customers to save targeted location criteria.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `customer_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `users.id`. |
| `name` | `VARCHAR(100)` | NOT NULL | Customer-defined label (e.g. "Airport LED Screens"). |
| `filters` | `JSON` | NOT NULL | JSON string representing filter states. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `customer_id` references `users(id)` on delete cascade.

---

#### Table 5: `marketplace_favorites`
User bookmarks for easy access during campaigns setup.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `customer_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `users.id`. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |
| `created_at` | `TIMESTAMP` | NULL | Bookmark timestamp. |

* **Foreign Keys**:
  * `customer_id` references `users(id)` on delete cascade.
  * `inventory_id` references `inventories(id)` on delete cascade.
* **Indexes**:
  * Unique Composite Index on `(customer_id, inventory_id)`.

---

#### Table 6: `marketplace_recent_views`
Lists screens recently viewed by the user.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `customer_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `users.id`. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |
| `viewed_at` | `TIMESTAMP` | NOT NULL | Viewed timestamp. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |

* **Foreign Keys**:
  * `customer_id` references `users(id)` on delete cascade.
  * `inventory_id` references `inventories(id)` on delete cascade.

---

## Future Scope

* **Table `marketplace_recommendations`**: AI recommendation logic storage (deferred to V2).
* **Table `marketplace_price_index`**: Historical price tracking for price-drop notification alerts (deferred to V2).
