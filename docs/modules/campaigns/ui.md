# Campaign Module: User Interfaces

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the visual layouts, panels, screen flow designs, and inputs required for the Campaign management portals.

---

## Scope

This document specifies UI screens visible to Customers, Providers, and Admins:
* Campaign Dashboard.
* Calendar Views (Day, Week, Month, and active screen schedules).
* Creative uploader & proof gallerist tools.
* Performance Completion report.

---

## Business Rules

### 1. Unified Dashboard Interfaces

#### Screen 1: Campaign Dashboard (Customer View)
* **Objective**: Primary overview of active advertising campaigns.
* **Layout**:
  * **Rows Grid**:
    * **Analytics Row**: Active flights counters, media upload checks, and loop frequency progress cards.
    * **Recent Tasks Alert List**: Alerts flashing text (e.g. "Creative Rejected by Branch India North - Re-upload required").
    * **Interactive Calendar**: Main calendar display logging screen schedule events.

---

### 2. Calendar Views Specification
The Campaign module must support four distinct calendar view toggles:
* **Day View**: Tracks screen slot index allocations (1 to 6) hour-by-hour for a single calendar date.
* **Week View**: Visual grid displaying loop blocks running Monday to Sunday.
* **Month View**: Overview highlighting active flight ranges in horizontal color blocks.
* **Inventory Schedule**: Mapped timeline showing which campaigns are running on a specific display screen, detailing available loop slots left.

---

### 3. Screen Layout Specifications

#### Screen 1: Artwork Uploader Form (Customer View)
* **Objective**: Submit ad creatives.
* **Layout**:
  * Drag-and-drop file uploader box.
  * Form inputs displaying supported file formats guide (JPG, PNG, MP4, PDF, AI, PSD, CDR, ZIP).
  * Auto-extracts file dimensions and outputs a warning notification if dimensions do not match the target display screen specs.

---

#### Screen 2: Proof Upload Pane (Provider View Only)
* **Objective**: Onload field verification proofs.
* **Layout**:
  * Dual-tab file picker allowing photo capture camera integrations or local S3 uploads.
  * Text notes field to log installation metrics (e.g. "Screen cleaned and playing loop successfully").
  * Action button: **Publish Execution Proof**.

---

#### Screen 3: Campaign Completion Report
* **Objective**: Final report card summarizing flight operations.
* **Layout**:
  * Dashboard grid showing:
    * Total flight days elapsed.
    * Total estimated consumer reach (daily footfall aggregation).
    * Photo Proofs slide-carousel.
    * Verification status timestamps.
    * Download Action: "Print Report / Export PDF Summary".

---

## Future Scope

* **Embedded Real-Time Video Player**: Web stream viewer showing live plays of ads on physical screen feeds (deferred to V2).
