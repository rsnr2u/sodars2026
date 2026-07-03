# Notifications Module: Workflows

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the operational workflows and logic steps for the centralized event-driven Notification routing engine.

---

## Scope

This document specifies step-by-step workflows for:
* Capturing system events.
* Loading templates and merging dynamic variables.
* Evaluating user channel preferences.
* Queue scheduling, gateway routing, and error logging.
* Automatic retry cycles.

---

## Business Rules

### 1. Unified Event Routing Workflow
The system processes all notifications through a single, standardized pipeline:

1. **System Event Trigger**:
   * A module executes a transaction (e.g. Booking payment verification) and fires a system event (e.g. `PaymentReceived`).
2. **Template Matching**:
   * The notification engine intercepts the event and queries the `notification_templates` table matching the event key.
3. **Preferences Filter**:
   * Engine queries `notification_preferences` matching the target recipient `user_id`.
   * *Critical Exemption*: Mandatory transaction alerts (e.g., booking receipts or password reset notifications) bypass preference checks and cannot be disabled.
4. **Variables Merging**:
   * Engine maps database entity variables (e.g., customer name, transaction amount) and replaces the variables markers in the template text:
     ```text
     "Hello {{customer_name}}, payment of {{amount}} is verified."
     -> "Hello Rajesh Kumar, payment of ₹35,400.00 is verified."
     ```
5. **Queue Insertion**:
   * System inserts record in the `notifications` table in `pending` status.
   * Inserts matching record in `notification_queue` with `attempts = 0` and status set to `queued`.
6. **Worker Dispatch**:
   * Background queue workers pick up the task, changing status to `processing`.
7. **Channel Routing & Gateway Delivery**:
   * Based on the template channel, the engine dispatches payloads to the respective external gateway provider API:
     * *Email*: SendGrid / Mailgun API.
     * *SMS*: Twilio API.
     * *WhatsApp*: Twilio WhatsApp API.
     * *Push*: Firebase Cloud Messaging API.
     * *In-App*: Writes alert record to database; UI updates notification bell count in real-time.
8. **Logging & Response Tracking**:
   * System writes the gateway response JSON payload to `notification_logs`.
   * If successful: Status shifts to `delivered`. Queue entry is removed.
   * If failed: Status shifts to `failed`. System writes errors stack trace to `notification_failures` and triggers the **Retry Workflow**.

---

### 2. Workflow: Retry Loop
* **Steps**:
  1. If a notification fail trigger executes:
     * System queries the attempt counter in `notification_queue`.
  2. If `attempts < 3`:
     * Increments the `attempts` count by 1.
     * Calculates exponential backoff retry time:
       * *Attempt 1*: Try again in 5 minutes.
       * *Attempt 2*: Try again in 15 minutes.
       * *Attempt 3*: Try again in 60 minutes.
     * Updates `next_attempt_at` and sets status back to `queued`.
  3. If `attempts >= 3` (Max Retries Exceeded):
     * Removes notification from active queue.
     * Sets status to `failed`.
     * Logs critical alerts to the system admin error desk.

---

## Future Scope

* **Smart Priority Queueing**: Prioritizing verification codes and booking receipt notifications over marketing broadcasts (deferred to V2).
