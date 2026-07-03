# Analytics Module: User Interfaces

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the visual layouts, panels, screen flow designs, and inputs required for the Analytics module in all user portals.

---

## Scope

This document specifies UI screens visible to Executives, Branch Managers, and Providers:
* Executive Dashboard (Head Office view).
* Branch Manager Dashboard.
* Provider Dashboard.
* Analytics detail dashboards (Revenue, Bookings, Campaigns, Notifications).
* Standard Chart elements styles.
* Export report popups.

---

## Business Rules

### 1. Screen Layout Specifications

#### Screen 1: Executive Dashboard (Head Office View)
* **Objective**: Global operations overview.
* **Layout**:
  * **Header**: Title "Executive Analytics Dashboard", calendar date-range picker, and a dropdown selecting target Branches (defaults to All Branches).
  * **Row 1: KPI Cards Grid**:
    * Gross Sales (with percentage change curve).
    * Total Screens count.
    * Average Network Occupancy Rate.
    * Active Campaigns.
  * **Row 2: Financial Charts**:
    * Left: Daily revenue timeline area chart.
    * Right: Donut chart displaying branch sales breakdown (Top 5 Branches).
  * **Row 3: Top 10 Rankings Table**: List of top performing screen assets by revenue and occupancy rates.

---

#### Screen 2: Branch Manager Dashboard
* **Objective**: Regional sales audit tools.
* **Layout**:
  * KPI row: Local Sales, active screens, local providers count, pending audits warnings.
  * **Calendar Heatmap**: Renders local screen inventory schedules, coloring days based on average occupancy density (e.g. Red for 100% booked, Green for open slots).
  * Table displaying active providers list with screen counts and payout indicators.

---

#### Screen 3: Provider Dashboard
* **Objective**: Screen revenue audits for display owners.
* **Layout**:
  * KPI widgets: Gross Payouts earned, current months pending payouts, active screens count.
  * **Stacked Bar Chart**: Displays monthly earnings showing Net Price sums.
  * Table outlining individual displays with booking slots details.

---

### 2. Standard Chart Types (Vanilla CSS styling wrappers)
All frontend portals must render data using the following standard charts:
* **Bar / Stacked Bar**: Used for comparing branch volumes or provider monthly revenues.
* **Line / Area Chart**: Tracks timelines (daily sales, bookings count growth).
* **Pie / Donut Chart**: Represents percentage shares (branch splits, display category allocations).
* **Calendar Heatmap**: Details date capacity density in booking diaries views.

---

#### Screen 4: Export Dialog Modal
* **Objective**: Interface to compile downloads parameters.
* **Layout**:
  * Form overlays listing:
    * Select Dataset (dropdown).
    * Select Date Range (From/To dates input).
    * Select Format (Radio buttons: PDF, Excel, CSV).
  * Button: **Generate & Download File**.

---

## Future Scope

* **Drag-and-drop dashboard customizer widgets**: Allowing users to drag, resize, and add custom chart widgets inside their dashboards (deferred to V2).
