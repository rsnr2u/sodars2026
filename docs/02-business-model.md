# 02. Business Model

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the SODARS business ecosystem, stakeholder relationships, organizational hierarchy, and financial transaction markup models.

---

## Scope

This document covers:
* Stakeholder definitions and structural hierarchy.
* Marketplace Pricing Model formulas and configurations.
* Platform fee generation logic and provider payout rules.

---

## Business Rules

### 1. Organizational Hierarchy
SODARS operates under a decentralized branch model governed by a central head office:

* **Head Office**:
  * Global system owner.
  * Manages global settings (e.g., default maximum markups, payment gateways).
  * Creates and manages Branches and assigns Branch Managers.
  * Oversees overall platform analytics and payouts.
* **Branches**:
  * Regional operational business units of SODARS.
  * Responsible for onboarding and verifying local Providers and their digital assets.
  * Manage local bookings, disputes, and branch-specific markups.
* **Providers**:
  * Screen and digital billboard owners/operators.
  * List their inventory, specify availability, and define their locked **Net Price**.
  * Earn the agreed Net Price for all approved bookings.
* **Customers**:
  * Advertisers (individuals, corporate entities, local businesses, political parties).
  * Browse the public marketplace, construct campaigns, and check out to buy ad slots.
* **Marketplace**:
  * The public-facing discovery portal connecting Customers with Providers.

### 2. Marketplace Pricing & Markup Model
To generate platform revenue, SODARS applies a markup to the Provider's Net Price:

* **Net Price ($P_{net}$)**:
  * Specified by the Provider when listing/editing an asset.
  * The Provider is guaranteed to receive this exact amount upon successful completion of a campaign.
* **Maximum Markup Percentage ($M_{max}$)**:
  * Configurable in the Admin settings.
  * The default global maximum markup is **20%**.
  * Can be overridden at the Branch level, but cannot exceed the global max threshold unless specifically authorized by the Head Office.
* **Marketplace Price ($P_{market}$)**:
  * The price shown to the Customer.
  * Formula:
    $$P_{market} = P_{net} \times (1 + \text{Markup Percentage})$$
  * Example (with default 20% markup):
    * Provider Net Price ($P_{net}$): **₹1,500**
    * Markup Percentage: **20%**
    * Marketplace Price ($P_{market}$): **₹1,500 * 1.20 = ₹1,800**
* **Revenue Splitting**:
  * Customer Pays: **₹1,800**
  * Provider Receives: **₹1,500**
  * SODARS Platform Fee: **₹300** (earning the difference)

---

## Future Scope

* **Franchise Split-Revenues**: Support for independent franchise operators running Branches, splitting the platform fee between Head Office and the Franchise Branch (e.g., 30/70 split).
* **Dynamic Pricing Engine**: Automated adjustment of markup percentages based on occupancy rate, high-demand seasons, or real-time event traffic.
