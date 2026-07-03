# ADR 002: Marketplace Pricing Engine

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 002: Marketplace Pricing Engine
* **Status**: Approved
* **Date**: 2026-06-29

---

## Context

We need to define a consistent, secure, and configurable system to calculate advertising screen prices. The system must guarantee provider revenue while generating platform earnings via markups, avoiding floating-point rounding errors and protecting pricing integrity.

---

## Decision

We will implement a **locked Net Price + configurable markup** engine:

* **Provider Net Price**: The Provider defines their locked Net Price when listing a screen. The Provider is contractually guaranteed to receive this exact Net Price for all approved bookings.
* **Configurable Markup**:
  * The Marketplace calculates the Marketplace Retail Price using a configurable markup percentage.
  * The global default Maximum Markup is **20%**, configurable directly from the Admin Settings database table.
  * Regional Branches have the authority to negotiate or set lower branch-specific markups between the Provider's Net Price and the Maximum Selling Price (Net Price + Max Markup).
* **Customer Presentation**: The Customer only sees the final Marketplace Price. The platform markup details and Net Price are hidden from public views.

---

## Consequences

* **Advantages**:
  * Protects Provider margins, building trust and encouraging display owners to list screen inventory.
  * Prevents transaction integrity bypass: Because calculations happen entirely on the backend API using database-validated Net Prices and Admin Settings config records, Customers cannot manipulate checkout prices.
  * Easy adjustments: Admins can update markups without modifying individual screen listings.
* **Disadvantages**:
  * Price changes do not auto-adjust active campaigns. If a markup configuration changes mid-campaign, it only affects future checkout carts.

---

## Future Notes

* Dynamic or automated price adjustment models based on demand and seasonality will be evaluated in Version 2.
* All currency figures must be stored in database tables in cents/paisa as integers.
