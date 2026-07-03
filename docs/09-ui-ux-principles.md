# 09. UI/UX Principles

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to specify the design rules, usability rules, and interactive patterns governing the user experience of all SODARS client interfaces.

---

## Scope

This document covers:
* Strategic design principles (Google Maps-first, 3-click rule, POS-style booking).
* Responsive layout guidelines and mobile hit boundaries.
* UI component structures (card layouts, minimal forms, consistent themes).

---

## Business Rules

### 1. Core Principles

* **Google Maps First**:
  * The primary marketplace discovery interface is a map. Users search by location, and screens are mapped dynamically.
  * Clicking a map marker slides open an asset info panel without forcing page reloads.
* **3-Click Rule**:
  * A user must be able to add an asset to their cart within 3 clicks of landing on the site.
  * Click 1: Click marker on map.
  * Click 2: Click "Quick Book" on drawer.
  * Click 3: Click "Add to Cart" on schedule configurator.
* **POS-Style Campaign Booking**:
  * Selecting runtime slots must behave like a Point of Sale (POS) cash register: highly visual grid layout, quick quantity/slot adjustments, immediate calculations, and no complex configuration wizards.
* **Unified Booking Cart**:
  * Multiple screens, across different locations and dates, can be consolidated into a single checkout cart and paid for in a single transaction.
* **Provider-First Dashboard**:
  * Providers must see clear summaries of screen performance (occupancy, uptime) and payout status immediately upon logging in. Forms to register screens must be conversational and simple.

### 2. UI Layout Guidelines
* **Mobile-First Design**:
  * All views must render on mobile-width devices (320px to 480px) and scale up to desktop screens dynamically.
* **Touch Target Sizing**:
  * Interactive elements (buttons, links, form selectors) must feature a minimum clickable bounding box of **48x48 pixels** to prevent misclicks on touch interfaces.
* **Card-Based UI Grid**:
  * Display screens, bookings, and reports as visual cards with standard rounding (`border-radius: 12px` or similar) and smooth transition scale hover effects.
* **Minimal Input Forms**:
  * Avoid long, intimidating forms. Break onboarding steps into progressive disclosures or tabs.
  * Use inline maps for location choice and autofill addresses via Geocoding.

---

## Future Scope

* **Dark/Light Mode Syncing**: System-level syncing of display theme settings.
* **Interactivity sound feedback**: POS-style sound alerts for successful screen scans and booking checkouts.
