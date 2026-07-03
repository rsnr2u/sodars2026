# Customer Module: Business Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the business logic rules, constraints, validations, and security limits applied to Customer profiles and order records.

---

## Scope

This document specifies the rules applied to self-registrations, default branch mappings, entity audit checkpoints, and data access bounds.

---

## Business Rules

### 1. Account Integrity & Verification Rules
* **Single Corporate/Individual Mapping**:
  * One Customer account represents exactly one distinct corporate advertiser or individual account. Multi-company consolidation is not permitted under a single customer profile in V1.
* **Default Branch Mapping**:
  * Upon registration, the customer is assigned to exactly **one default Branch** matching their city.
  * Local branch managers govern the customer's account audit tasks and regional dispute clearances.
* **Category Audit Checkpoint**:
  * Individual and Corporate category accounts are active immediately upon registration.
  * Government and Political Party category accounts require compliance audit. The system blocks checkout bookings requests until the customer's uploaded documentation has been verified and marked `approved` by their default Branch Manager.

---

### 2. Data Isolation & Security Boundaries
* **Customer Isolation**:
  * A Customer can only view their own profile, settings, uploaded creative files, order lists, and billing invoice documents.
  * Accessing another customer's campaign details or checkout receipts is strictly prohibited.
* **Branch Manager Boundaries**:
  * Branch Managers only see Customers whose default branch matches their assigned `branch_id`.
* **Head Office Boundaries**:
  * Super Admin (Head Office) has absolute global visibility and can query all customer accounts.

---

## Future Scope

* **Postpaid Terms**: Validation logic checking available credit balances for certified agency customers (deferred to V2).
