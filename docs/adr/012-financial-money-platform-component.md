# ADR 012: Financial Money Platform Component

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 012: Financial Money Platform Component
* **Status**: Approved
* **Date**: 2026-07-01

---

## Context

Financial transactions, invoice items, provider payouts, and platform commission rates are calculated across several modules (Bookings, Wallet, Settlements, Finance, Analytics). To ensure calculations are consistent and values are not rounded incorrectly or scattered across codebases, a centralized financial library is required.

---

## Decision

We decouple core value objects from mathematical services:

### 1. Value Object Reuse
We maintain the immutable `Money` and `Currency` value objects in the Core namespace (`App\Core\ValueObjects`). We do not duplicate them.

### 2. Platform Money Services
We host tax, discount, commission calculators, and rounding rules inside `Platform/Money`:
* **TaxCalculator**: GST calculations (tax-inclusive/exclusive pools).
* **DiscountCalculator**: absolute and relative percentage price deductions.
* **CommissionCalculator**: platform fee commission percentages and partner splits.
* **FinancialSummary**: Immutable value object wrapping core money fields (subtotal, discount, tax, platform fee, provider share, commission, grand total) to be consumed universally by Bookings, Invoices, and Settlements.
* **RoundingPolicy**: Enforces rounding behaviors (Half Up, Ceiling, Floor).

---

## Consequences

* **Advantages**:
  * Guarantees identical financial math across bookings, wallets, and statements.
  * Preserves single source of truth for Money and Currency value objects.
  * Decouples tax and commission calculation logic from database models.
* **Disadvantages**:
  * Requires explicit dependencies on Platform/Money across modules, but prevents rounding errors and calculation discrepancies.
