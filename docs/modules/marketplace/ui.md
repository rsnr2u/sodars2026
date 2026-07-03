# Marketplace Module: User Interfaces

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the visual layouts, templates, responsive screens, and POS cart interactions for the Marketplace module.

---

## Scope

This document specifies UI screens visible to public visitors and logged-in customers:
* Map-First search interface.
* Sidebar filter panels.
* Display cards and info sliders.
* Compare overlay grid.
* POS-Style slide-out Checkout Cart.
* Favorites list.
* Booking submission results.

---

## Business Rules

### 1. Unified Interface Layout

#### Screen 1: Marketplace Home & Map Discovery
* **Layout**:
  * **Header**: Navigation bar containing the SODARS branding, "Favorites" icon, "Booking Cart" icon, and Customer Profile settings dropdown.
  * **Map Container**: Google Maps fills the body area, rendering dynamic markers.
  * **Sidebar (Left-Floating)**:
    * Text search input (integrated with Google Places autocomplete for locations).
    * Filter collapse buttons (Price slide bar, orientation select, availability calendar range).
  * **Cards Grid (Lower Screen Overlay)**: A swipeable horizontal list of screen cards matching the visible map boundary. Swiping cards centers the map on the respective screen pin.

---

#### Screen 2: Compare Overlay Grid
* **Objective**: Side-by-side evaluation of selected displays.
* **Layout**:
  * Triggered by selecting "+ Compare" check boxes on cards.
  * Launches an overlay grid showing up to 3 screens.
  * Columns represent selected screens with:
    * Site Photo.
    * City/Area address.
    * Physical screen size and pixel layout.
    * Average daily footfalls.
    * Baseline daily Retail Cost.
  * Click button at columns footer: "Add Selected to Booking Cart".

---

#### Screen 3: POS-Style Booking Cart Drawer
* **Objective**: Slide-out pane housing select bookings details.
* **Layout**:
  * Triggered by clicking the Cart icon. Slides out from the right boundary.
  * **Cart Line Items**: Each card shows:
    * Thumbnail photo.
    * Screen Title & City.
    * Flight Date range (e.g. Oct 1 - Oct 5).
    * Daily Frequency multiplier control (e.g., `[-] 2 plays/hour [+]`).
    * Computed item cost:
      $$\text{Item Cost} = \text{Daily Retail Price} \times \text{Days} \times \text{Plays/Hour}$$
    * "Remove [x]" trash bin icon.
  * **Footer section**:
    * Tax line item display.
    * Order Total display.
    * **Action Button**: "Submit Booking Request".

---

#### Screen 4: Thank You / Receipt Screen
* **Objective**: Confirm checkout requests submission.
* **Layout**:
  * Large check badge confirming request submission.
  * Summary invoice details displaying booking request ID, audit stage trackers, S3 upload file confirmations, and target branch manager contact coordinates.

---

## Future Scope

* **Virtual AR Preview Drawer**: Virtual reality preview pane allowing mobile users to see their ads overlaying physical screen models (deferred to V2).
