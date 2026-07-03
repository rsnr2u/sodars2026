# Customer Module: Workflows

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the operational workflows and logic steps for the Customer module, tracing customer actions from self-registration to invoice downloads.

---

## Scope

This document specifies step-by-step logic, verification gates, and conditions for:
* Customer registration and auto branch mapping.
* Category verification locks for Government and Political accounts.
* Profile updates.
* Reviewing orders and retrieving billing receipts.

---

## Business Rules

### 1. Workflow: Customer Registration
* **Actor**: Advertiser (User).
* **Steps**:
  1. User fills in registration form: Name, Email, Password, Category, Address, City.
  2. System checks database:
     * *Validation*: Email must be unique.
  3. System maps user city to `branch_coverage_cities` to define their default managing branch.
  4. System registers User credentials, creates matching profile entry in the `customers` table with status `active`.

---

### 2. Workflow: Category Verification Audit
* **Actor**: Customer Admin / Branch Manager.
* **Steps**:
  1. If customer signs up as `government` or `political_party`:
     * System marks profile status as `pending_verification`.
     * System blocks Customer from submitting cart checkout requests.
  2. Customer uploads verification credentials (authorization letter/certificate). Files upload directly to S3; records are logged in `customer_verification_docs`.
  3. Branch Manager views audit list, checks documents, and clicks **Approve** or **Reject**:
     * *Approve Action*: Status switches to `approved`. Customer profile status changes to `active`. Booking checkout triggers are unlocked.
     * *Reject Action*: Document status shifts to `rejected`. Customer receives notification to re-upload files.

---

### 3. Workflow: View Invoices & Booking History
* **Actor**: Customer Admin.
* **Steps**:
  1. Customer visits their portal dashboard.
  2. Clicks **Order History**.
  3. System retrieves all bookings records where `customer_id` matches the user's ID.
  4. Customer clicks **Download Invoice** on a booking line item:
     * System compiles booking detail metrics (daily retail price, days, frequency, taxes).
     * Generates standard PDF invoice and dispatches file stream to browser.

---

## Future Scope

* **Automatic Business Tax ID verification**: Direct connection with tax APIs to auto-verify business registrations (deferred to V2).
