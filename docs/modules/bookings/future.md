# Booking Module: Future Scope

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to list future requirements and transaction features for Bookings that are out of scope for Version 1.

---

## Scope

This document specifies:
* Out-of-scope payment gateway architectures.
* Future booking renewals.

---

## Business Rules

### 1. Deferred Features (Out of Scope for V1)

* **Integrated Online Payment Gateways**:
  * Real-time checkout integrations (Stripe, Razorpay) validating payments instantly.
* **Partial Payments & Deposits**:
  * Enabling customers to pay a 10% booking reservation deposit, with the remaining 90% balance due 7 days before flight start.
* **Automated Refund Routines**:
  * Triggering automatic Stripe refunds if a screen goes offline or if a booking is cancelled within approved cancellation periods.
* **EMI & Installments options**:
  * Direct merchant financing integration enabling customers to pay in installments.
* **Postpaid credit billing**:
  * Letting verified agencies checkout up to their account credit limits, issuing consolidated monthly invoices.
* **Subscription Campaigns & Auto-Renewals**:
  * Support for recurring monthly campaigns (e.g. "Run this ad on this screen every month until cancelled").
  * Automated recurring payments charging the customer's saved credit card.

---

## Future Scope

* Re-evaluate these requirements during Version 2 scoping sessions.
