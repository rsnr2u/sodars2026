# Shared Module: Business Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the business logic rules, constraints, validations, and security limits applied to Shared infrastructure utilities.

---

## Scope

This document specifies the rules governing file limits, master geographic dependencies, and log protections.

---

## Business Rules

### 1. Zero Business Logic Mandate
* The Shared module must remain independent of specific commercial rules. Defining pricing formulas, commission rates, or user approval checks inside the Shared module is prohibited.

---

### 2. File Upload & Thumbnail Rules
* **Centralized Upload Pipeline**: All upload requests must route through the Shared Upload Manager.
* **Size Restrictions**:
  * Ad creative videos: Maximum file size allowed is **50MB**.
  * Images: Maximum file size allowed is **5MB**.
* **Auto-Thumbnail Generation**:
  * The Image Manager must automatically generate a `150x150px` JPEG thumbnail for every graphic file upload. Thumbnails are saved in S3 with a `_thumb` file suffix.
* **Temporary Files Life Expiry**:
  * Temporary files stored in S3 (e.g. bulk CSV uploads) must have a mapped DB row in `temporary_files` containing a 24-hour expiration timestamp. System schedulers wipe expired files daily.

---

### 3. Log Protection and Immutability
* **Audit Logs Write-Once Restriction**:
  * Audit logs records in `audit_logs` are append-only.
  * Update, Delete, or Modify queries targeting the audit log table are blocked at the database driver configuration level to prevent database manipulation.
* **Activity Logs Append-Only**:
  * User activity logs are logged sequentially. Records cannot be updated.

---

### 4. Geographic Dependencies
* **Hierarchical Validity**:
  * Geographic mappings must adhere strictly to the database hierarchy:
    $$\text{Country} \rightarrow \text{State} \rightarrow \text{District} \rightarrow \text{City} \rightarrow \text{Pincode}$$
  * A Pincode or City record cannot exist in the database without referencing its parent geographic records.

---

### 5. Document & Data Integrity
* **Immutable Document Generation**:
  * Automatically compiled PDF invoices are written to S3 as read-only assets. Modifying generated PDFs is prohibited.
* **CSV Import Auditing**:
  * System must validate data rows completely before executing database inserts. Any validation error must abort the transaction.

---

## Future Scope

* **Automatic GPS distance variance alerts**: Notifying managers if screen geolocations vary from physical city boundaries (deferred to V2).
