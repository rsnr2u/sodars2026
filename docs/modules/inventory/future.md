# Inventory Module: Future Scope

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to list future enhancements, telemetry designs, and spatial integrations for display screens that are out of scope for Version 1.

---

## Scope

This document specifies:
* Out-of-scope media processing capabilities.
* Future analytics models.
* AI recommendation engines.

---

## Business Rules

### 1. Deferred Features (Out of Scope for V1)

* **Drone Image Integration**:
  * Specialized uploads for 360-degree aerial drone photography of screen junctions.
* **Google Street View Embedded Layer**:
  * Direct client integrations fetching active Google Street View coordinate frames so advertisers can check visibility blockages (trees, buildings) virtually.
* **AI Image Quality Auditor**:
  * Automating photo verification. An AI model scans provider upload photos to verify screen existence, orientation, and readability.
* **Traffic & Visibility Scores**:
  * Interfacing with cellular telemetry or local traffic speed detectors to estimate screen vehicle footfall counts dynamically.
  * Score ratings from 1-10 mapped to listings cards (e.g., "CP Screen: Visibility Score 9.5").
* **Audience Demographics**:
  * Integration with public location demographic platforms to show gender, age, and interests distributions on search tooltips.
* **Geographical Heatmaps**:
  * Interactive overlay filters showing localized consumer footfall maps directly inside the marketplace search maps.
* **Digital Twin & AR Preview**:
  * 3D rendering of billboards in virtual spaces.
  * Augmenting camera frames on mobile apps to let customers preview custom banners on physical screens in real-time.
* **AI Rate Recommendations**:
  * Machine learning algorithms advising providers to modify prices based on occupancy trends (e.g., "Change baseline to ₹2,800 to increase occupancy by 25%").

---

## Future Scope

* Re-evaluate these requirements during Version 2 scoping sessions.
