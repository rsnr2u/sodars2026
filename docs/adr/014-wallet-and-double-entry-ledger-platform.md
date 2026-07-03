# ADR 014: Wallet and Double-Entry Ledger Platform

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 014: Wallet and Double-Entry Ledger Platform
* **Status**: Approved
* **Date**: 2026-07-01

---

## Context

Financial platforms require mathematically verifiable audit trails for all monetary operations (deposits, payouts, settlement credits, refunds). Relying on simple database row updates (e.g. `wallets.balance = balance + amount`) makes historical audits impossible and is prone to synchronization and rounding errors. We need a general double-entry ledger platform component that guarantees transactional integrity.

---

## Decision

We establish the following platform components and rules:

### 1. General Ledger Component (`App\Platform\Ledger`)
We introduce a reusable double-entry booking system:
* **LedgerAccount**: Classifies accounts as Asset, Liability, Equity, Revenue, or Expense in the Chart of Accounts (COA).
* **LedgerJournal**: Holds header details (Reference, Narration, Source Module, Source ID, Accounting Period).
* **LedgerEntry**: Records credit or debit lines with polymorphic origin references (`ledgerable_id` & `ledgerable_type`).
* **Balance Verification**: The sum of all debits must equal the sum of all credits for any journal entry. This check is enforced programmatically by the `PostingEngine` before writing to the database.

### 2. Accounting Periods (Close/Lock)
Introduces `accounting_periods` table. When an accounting month is locked, the `PostingEngine` throws an exception, preventing retro-active balance alterations.

### 3. Calculated Wallet Balances
Wallets do not have a mutable `balance` column. 
A wallet's current balance is derived dynamically by calculating the sum of debit and credit entries associated with the wallet's linked Liability account in the ledger:
$$\text{Wallet Balance} = \text{Credits} - \text{Debits}$$
This isolates the wallet from direct edits and ensures that every change in balance maps to an immutable journal entry.

### 4. Integrated Payout Flow
Settlements and withdrawals map directly into the ledger:
* **Settlement Credit**: Debits Settlement Payables (Liability) and Credits the Provider's Wallet Liability account.
* **Withdrawal Completion**: Debits the Provider's Wallet Liability account and Credits Cash/Bank (Asset).

---

## Consequences

* **Advantages**:
  * Banking-grade verifiability and audit trail.
  * Guarantees that sum of all ledger assets matches liability plus equity.
  * Protects wallets against race conditions and concurrent write balance corruption.
* **Disadvantages**:
  * Slightly higher database reads due to sum aggregations, which is resolved via indexes and caching.
