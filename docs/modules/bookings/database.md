# Booking Module: Database Design

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the relational database schemas, columns, constraints, foreign key mappings, and indexes for the Booking module of SODARS.

---

## Scope

This document specifies seven tables: `bookings`, `booking_items`, `booking_status_history`, `booking_documents`, `booking_notes`, `booking_payments`, and `booking_logs`. It does not duplicate fields from Customer or Inventory profiles.

---

## Business Rules

### 1. Database Schema Specifications

#### Table 1: `bookings`
Primary transaction records tracking status and totals.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `customer_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `customers.id`. |
| `branch_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to managing `branches.id`. |
| `total_price_cents` | `INT` | NOT NULL | Total transaction cost in cents/paisa (excluding tax). |
| `status` | `VARCHAR(30)` | NOT NULL, DEFAULT "pending" | Status (`pending`, `branch_review`, `provider_review`, `approved`, `rejected`, `cancelled`, `in_progress`, `completed`). |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |
| `deleted_at` | `TIMESTAMP` | NULL | Soft delete support. |

* **Foreign Keys**:
  * `customer_id` references `customers(id)` on delete restrict.
  * `branch_id` references `branches(id)` on delete restrict.
* **Indexes**:
  * Index on `status`.

---

#### Table 2: `booking_items`
Individual screen items selected in the transaction. Copies values at checkout to prevent historical price alterations.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string. |
| `booking_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `bookings.id`. |
| `inventory_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `inventories.id`. |
| `start_date` | `DATE` | NOT NULL | Campaign flight begin date. |
| `end_date` | `DATE` | NOT NULL | Campaign flight end date. |
| `daily_frequency` | `INT` | NOT NULL | Target daily play loop slots. |
| `net_price_cents` | `INT` | NOT NULL | Locked Net Price copy of the display at checkout. |
| `markup_percentage` | `INT` | NOT NULL | Locked Branch Markup percentage copy at checkout. |
| `retail_price_cents` | `INT` | NOT NULL | Retail Price copy at checkout. |
| `total_item_price_cents`| `INT` | NOT NULL | Calculated line item cost. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |

* **Foreign Keys**:
  * `booking_id` references `bookings(id)` on delete cascade.
  * `inventory_id` references `inventories(id)` on delete restrict.

---

#### Table 3: `booking_status_history`
Audit log tracking workflow status changes.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string. |
| `booking_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `bookings.id`. |
| `changed_by` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Mapped user ID of auditor. |
| `from_status` | `VARCHAR(30)` | NOT NULL | Previous state. |
| `to_status` | `VARCHAR(30)` | NOT NULL | Updated state. |
| `comment` | `TEXT` | NULL | Text comments (mandatory on rejection/cancellation). |
| `created_at` | `TIMESTAMP` | NOT NULL | Insertion timestamp. |

---

#### Table 4: `booking_documents`
Payment reference receipts, invoice PDFs, or certificates.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `booking_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `bookings.id`. |
| `doc_type` | `VARCHAR(50)` | NOT NULL | Type (`payment_receipt`, `invoice_pdf`). |
| `file_path` | `VARCHAR(255)` | NOT NULL | S3 storage identifier. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |

---

#### Table 5: `booking_notes`
Internal comments between Branch Managers and Providers.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `booking_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `bookings.id`. |
| `author_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to global `users.id`. |
| `note_text` | `TEXT` | NOT NULL | Comment text. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |

---

#### Table 6: `booking_payments`
Manual ledger recording offline transactions in Version 1.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `booking_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `bookings.id`. |
| `payment_method` | `VARCHAR(30)` | NOT NULL | Method (`cash`, `bank_transfer`, `cheque`, `upi`, `neft`, `rtgs`). |
| `amount_cents` | `INT` | NOT NULL | Verified amount in cents/paisa. |
| `reference_number` | `VARCHAR(100)` | NOT NULL | Bank transaction ID, cheque index code. |
| `status` | `VARCHAR(20)` | NOT NULL, DEFAULT "pending" | State (`pending`, `verified`, `failed`). |
| `recorded_by` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Admin user ID who validated payment receipt. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |

---

## Future Scope

* **Table `booking_refunds`**: Deferring automated billing credit returns logs to V2.
