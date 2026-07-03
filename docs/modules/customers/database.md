# Customer Module: Database Design

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to define the relational database schemas, columns, constraints, foreign key mappings, and indexes for the Customer module of SODARS.

---

## Scope

This document specifies three tables: `customers`, `customer_billing_addresses`, and `customer_verification_docs`.

---

## Business Rules

### 1. Database Schema Specifications

#### Table 1: `customers`
Stores the billing profile and entity classification of each advertiser.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID v4 string identifier. |
| `user_id` | `CHAR(36)` | FOREIGN KEY, UNIQUE, NOT NULL | Link to global auth `users.id`. |
| `name` | `VARCHAR(150)` | NOT NULL | Display name (Individual Name or Business Name). |
| `category` | `VARCHAR(30)` | NOT NULL | Category (`individual`, `corporate`, `government`, `political_party`). |
| `default_branch_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Regional branch governing their billing transactions. |
| `tax_id` | `VARCHAR(50)` | NULL | Corporate tax ID (GSTIN, TIN) if business entity. |
| `status` | `VARCHAR(20)` | NOT NULL, DEFAULT "active" | Account status (`active`, `suspended`, `deactivated`). |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |
| `deleted_at` | `TIMESTAMP` | NULL | Soft delete support. |

* **Foreign Keys**:
  * `user_id` references `users(id)` on delete cascade.
  * `default_branch_id` references `branches(id)` on delete restrict.
* **Indexes**:
  * Index on `category`.
  * Index on `status`.

---

#### Table 2: `customer_billing_addresses`
Stores the official billing address data for VAT/GST invoices.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `customer_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `customers.id`. |
| `street_address` | `VARCHAR(255)` | NOT NULL | Street details. |
| `city` | `VARCHAR(100)` | NOT NULL | City location. |
| `state` | `VARCHAR(100)` | NOT NULL | State/Province. |
| `zip_code` | `VARCHAR(20)` | NOT NULL | Postal index code. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `customer_id` references `customers(id)` on delete cascade.

---

#### Table 3: `customer_verification_docs`
Stores regulatory documents uploaded by Government and Political advertisers.

| Column Name | Data Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `CHAR(36)` | PRIMARY KEY | Unique UUID. |
| `customer_id` | `CHAR(36)` | FOREIGN KEY, NOT NULL | Link to `customers.id`. |
| `doc_type` | `VARCHAR(50)` | NOT NULL | Doc code (e.g. `party_affiliation`, `department_letter`). |
| `file_path` | `VARCHAR(255)` | NOT NULL | S3 storage key. |
| `status` | `VARCHAR(20)` | NOT NULL, DEFAULT "pending" | Status (`pending`, `approved`, `rejected`). |
| `comment` | `TEXT` | NULL | Audit comments from Branch Manager. |
| `created_at` | `TIMESTAMP` | NULL | Insertion timestamp. |
| `updated_at` | `TIMESTAMP` | NULL | Update timestamp. |

* **Foreign Keys**:
  * `customer_id` references `customers(id)` on delete cascade.

---

## Future Scope

* **Table `customer_credit_limits`**: Out of scope for V1. Records credit limits and balances for postpaid clients.
