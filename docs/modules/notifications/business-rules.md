# Notifications Module: Business Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the business logic rules, constraints, validations, and security limits applied to Notifications.

---

## Scope

This document specifies the rules governing events, preferences, branch constraints, and variables.

---

## Business Rules

### 1. Architectural Integrity & Reuse Rules
* **Strict Reusability**: The notification engine must utilize a single centralized template table. Adding hardcoded email templates in code controllers is prohibited.
* **Dynamic Variable Merging**:
  * Variables must follow the `{{variable_name}}` double-bracket syntax.
  * System must support variables: `{{customer_name}}`, `{{provider_name}}`, `{{booking_number}}`, `{{campaign_name}}`, `{{inventory_name}}`, `{{branch_name}}`, `{{amount}}`, `{{payment_reference}}`, `{{date}}`, and `{{company_name}}`.
* **Immutability**: Once a notification is marked `delivered` or `failed`, its body, recipient, and channel parameters are immutable. Logs must not be overwritten or modified.

---

### 2. Retries & Failures Policies
* **Automatic Queue Retries**:
  * If a gateway dispatch returns an API fail state (e.g. SMTP timeout or Twilio error), the queue worker increments the attempts counter.
  * The maximum retry ceiling is **3 attempts**.
  * Retries must follow an exponential backoff schedule: 5 minutes, 15 minutes, and 60 minutes.
  * If attempts reach 3, the notification status shifts to `failed` and is cataloged in `notification_failures`.

---

### 3. Channel Preference Rules
* **Opt-Out Control**: Users can toggle off channels (e.g., SMS alerts) inside the profile page.
* **Mandatory Alerts**:
  * Critical transaction notifications (e.g., `PaymentReceived`, `CreativeRejected`, `BookingApproved`, `ForgotPassword`) cannot be disabled. The API must validate preference updates to block deactivation of these event mappings.

---

### 4. Jurisdiction & Announcements
* **Branch Announcements**:
  * Broadcast announcements created by a Branch Manager are routed only to Users (Providers/Customers) assigned to that specific `branch_id`.
* **System Announcements**:
  * Broadcast announcements created by Super Admin (Head Office) are delivered to all active users globally.

---

## Future Scope

* **Automatic SMS to WhatsApp failover**: Automatically routing failed SMS dispatches to WhatsApp if the user has WhatsApp enabled (deferred to V2).
