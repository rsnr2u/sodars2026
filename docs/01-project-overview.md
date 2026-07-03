# 01. Project Overview

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to introduce the Smart Outdoor Digital Asset Resource System (SODARS), defining the high-level objectives, core operational modules, and primary workflows for managing outdoor digital advertising assets.

---

## Scope

This document covers:
* High-level system objectives.
* Core system actors and target audiences.
* System-wide workflow from asset listing to booking completion.
* Architectural boundaries for Version 1.

---

## Business Rules

### 1. Project Objectives
* **Automation**: Replace manual sales negotiations and physical site inspections with automated web-based and mobile workflows.
* **Geographical Mapping**: All digital assets must be displayed visually using a Google Maps interface for accurate regional targeting.
* **Operational Control**: Ensure localized branches can govern regional networks, pricing, and local provider approvals.

### 2. High-Level Workflow
* **Onboarding**: Providers register and submit their outdoor digital assets (screens, LED boards, billboards) for verification.
* **Verification & Approval**: Branches inspect and approve the registered assets, checking dimensions, operating hours, geolocation, and technical constraints.
* **Listing**: Approved assets are published to the public Marketplace.
* **Discovery & Booking**: Customers search the Marketplace, add digital assets to their cart, configure campaigns (dates, slot frequency), and complete the booking.
* **Execution**: Approved creative content is delivered to the provider's screens via integrations (or manual upload notification workflows for V1).

---

## Future Scope

* Direct API integrations with Digital Signage Player softwares (e.g., BrightSign, Broadsign, custom android players) to play creative assets automatically.
* Advanced live-camera proof-of-performance modules.
