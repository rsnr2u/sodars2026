# Branch Module: User Interfaces

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the visual layouts, panels, screen flow designs, and fields required for the Branch module interfaces in the Admin/Branch Portal.

---

## Scope

This document specifies UI screens visible to Super Admins and Branch Managers:
* Branch List View.
* Add / Edit Branch Forms.
* Branch Dashboard.
* Coverage Area Panel.
* Assigned Providers & Inventory grids.
* Branch Staff management.
* Branch Settings panel.

---

## Business Rules

### 1. Screen Layout Specifications

#### Screen 1: Branch List (Super Admin Only)
* **Objective**: Display all branches with their activation states and codes.
* **Layout**:
  * **Header**: "Branches" Title, count card indicator, and a "+ Add Branch" button.
  * **Table/Grid**: Responsive card grid of active/inactive branches showing:
    * Branch Name & Code.
    * Assigned Manager name.
    * Total screens count.
    * Status Badge (Green "Active" / Grey "Deactivated").
    * Action items: "Edit", "Configure Coverage", "Manage Staff".

---

#### Screen 2: Add / Edit Branch Form (Super Admin & Branch Manager)
* **Objective**: Create or update branch settings.
* **Layout**:
  * Clean, single-column tabbed layout.
  * **Tab 1: Profile Info**:
    * Name (Text input).
    * Unique Branch Code (Text input, locked on edit).
    * Support Email and Support Phone inputs.
  * **Tab 2: Localization**:
    * Timezone (Searchable dropdown).
    * Currency Code (Locked to "INR" for V1, dropdown menu for future).
    * Markup Percentage (Numeric input slider, range 0% - 20%).
  * **Save Button**: Triggers validation and database write.

---

#### Screen 3: Branch Dashboard (Branch Manager View)
* **Objective**: Landing page for regional managers.
* **Layout**:
  * **Row 1: KPI Cards**:
    * Gross Sales this month.
    * Current Screen Occupancy percentage.
    * Total Onboarded Providers.
    * Pending Screen Approvals count (blinks red if > 0).
  * **Row 2: Action list & Quick Charts**:
    * Screen checklist table showing pending review inventory.
    * Interactive calendar logging upcoming booked campaigns.

---

#### Screen 4: Coverage Area Panel (Branch Manager View)
* **Objective**: Manage cities governed by the branch.
* **Layout**:
  * **Input Form**: City Name (Autopopulated text input via Google Places autocomplete), State Name (Disabled auto-filled field).
  * **List Grid**: Chip components representing active coverage cities (e.g., `Noida [x]`, `Gurgaon [x]`). Clicking the `[x]` triggers a delete modal confirmation.

---

#### Screen 5: Assigned Providers & Inventory Grids
* **Objective**: Track local assets.
* **Layout**:
  * Tabbed split table.
  * **Tab 1: Providers**: List of providers in the branch's coverage area, showing onboarding state and total screens.
  * **Tab 2: Inventory**: List of digital screens, showing status (Draft/Pending/Approved), current Net Price, and active occupancy rates.

---

#### Screen 6: Branch Staff
* **Objective**: Add regional assistants.
* **Layout**:
  * Table displaying name, email, role (Manager/Staff), and last login timestamp.
  * Action button: "+ Add Staff". Opens a modal to input user details and role.

---

## Future Scope

* **Branch Maps Layer**: Interactive branch-specific GIS boundary line drawings on a visual dashboard map showing exact administrative jurisdiction zones.
