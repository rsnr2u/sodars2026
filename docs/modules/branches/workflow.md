# Branch Module: Workflows

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the operational workflows and state-transition steps for managing Branches, coverage areas, provider routing, and inventory transfers.

---

## Scope

This document details the step-by-step logic, actors, actions, and validation gates for:
* Branch management (Create, Update, Deactivate).
* Regional coverage setup.
* Provider allocation.
* Inter-branch inventory transfers.
* Branch-level dashboard calculations.

---

## Business Rules

### 1. Workflow: Create Branch
* **Actor**: Super Admin (Head Office).
* **Steps**:
  1. Super Admin inputs: Branch Name, Branch Code, Timezone, Currency Code, default Markup Percentage, Support Email, and Support Phone.
  2. The system checks database constraints:
     * *Validation*: Name and Code must be globally unique.
     * *Validation*: Markup Percentage must be an integer between 0 and 20.
  3. System generates a new UUID v4.
  4. System inserts record into the `branches` table.
  5. System triggers a confirmation log.

---

### 2. Workflow: Update Branch
* **Actor**: Super Admin or Branch Manager.
* **Steps**:
  1. User modifies editable fields (e.g., Support Email, Support Phone, timezones, markup).
  2. The system verifies authorization:
     * *Validation*: Branch Managers can only modify their assigned branch.
     * *Validation*: The new markup value cannot exceed the system-configured maximum markup (default 20%).
  3. System updates database and dispatches a cache-clear event for screen pricing metrics.

---

### 3. Workflow: Deactivate Branch
* **Actor**: Super Admin.
* **Steps**:
  1. Super Admin toggles the branch status to `is_active = 0`.
  2. The system initiates cascade logic:
     * Sets all screen assets assigned to this `branch_id` to hidden status in search query results.
     * Permits active campaigns to run to their flight end dates.
     * Prevents Customers from checking out new carts containing screens under this branch.
  3. System triggers email notifications to all active providers registered under this branch informing them of deactivation.

---

### 4. Workflow: Branch Coverage
* **Actor**: Branch Manager.
* **Steps**:
  1. Branch Manager enters City and State (e.g., "Noida", "Uttar Pradesh").
  2. The system validates the entry:
     * *Validation*: The combination of `(branch_id, city_name)` must not already exist.
  3. System inserts the city into `branch_coverage_cities`.
  4. The city becomes selectable on the public marketplace location lookup filters.

---

### 5. Workflow: Assign Provider
* **Actor**: System / Branch Manager.
* **Steps**:
  1. A new Provider self-registers and inputs their business address city.
  2. The system queries `branch_coverage_cities` matching the provider's city.
  3. System assigns the provider to the matching `branch_id` as their default managing branch.
  4. The assigned Branch Manager receives a notification alert to verify the Provider's uploaded identity documentation.

---

### 6. Workflow: Transfer Inventory Between Branches
* **Actor**: Super Admin.
* **Steps**:
  1. Super Admin selects a screen asset and triggers a Branch Transfer.
  2. Admin inputs the target `branch_id`.
  3. The system executes integrity checks:
     * *Validation*: Target branch must be active (`is_active = 1`).
  4. System updates the screen's `branch_id` foreign key.
  5. The target branch's markup settings are immediately applied to the screen for all future bookings. Past booking logs remain attributed to the original branch.

---

### 7. Workflow: Branch Dashboard & Performance
* **Actor**: Branch Manager.
* **Steps**:
  1. Manager visits dashboard.
  2. System runs query calculations:
     * Aggregates screen counts, active provider counts, and current month gross bookings.
     * Calculates the daily screen occupancy rate metrics.
  3. Renders cards and performance graphs representing local KPIs.

---

## Future Scope

* **Auto-Routing Fallbacks**: Automated re-routing of provider assignments to a secondary branch if their primary city's branch is deactivated.
