# Notifications Module: User Interfaces

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the visual layouts, panels, screen flow designs, and inputs required for the Notifications module in all portals.

---

## Scope

This document specifies UI screens visible to Users and Admins:
* In-App Notification Center (Bell Dropdown).
* Notification Preferences pane.
* Template Manager (Admin).
* Delivery Logs & Queue status tracker (Admin).
* Broadcast Composer (Admin).

---

## Business Rules

### 1. Screen Layout Specifications

#### Screen 1: Notification Center (Floating Bell Dropdown)
* **Objective**: Fast lookup of recent unread alerts.
* **Layout**:
  * Positioned in header next to user profile. Shows a red badge count indicator (e.g. "3").
  * Clicking the bell toggles a slide-out menu.
  * Displays a list of recent notification cards showing:
    * Action icon (Green check, blue invoice, red warning).
    * Title text and relative time label (e.g., "5 mins ago").
    * Text snippet.
    * Click Action: Redirects to target transaction and marks item as read automatically.
  * Footer link: "View All Notifications".

---

#### Screen 2: Notification Preferences Pane (All User Profiles)
* **Objective**: Let users customize alert channels.
* **Layout**:
  * Structured as a settings checklist grid.
  * Columns: Notification Event (e.g., screen approval, booking submission), email toggle, SMS toggle, push alert toggle.
  * *UI Guard*: If an event is designated as a critical transaction, the toggle switches are locked in active state with a tooltip note: "Mandatory billing alerts cannot be disabled".

---

#### Screen 3: Template Manager (Admin Console Only)
* **Objective**: Modify email templates body structures.
* **Layout**:
  * Left sidebar listing events (e.g. `New Booking Request`, `Creative Rejected`).
  * Right pane displaying form options:
    * Select Channel Tab (Email, SMS, Push).
    * Subject Template Input box (incorporating variables).
    * Body Template Editor: Rich-text text area where admins input message layouts and double-bracket merge keys:
      ```text
      "Hi {{customer_name}}, thank you for booking screen {{inventory_name}}."
      ```
    * Sidebar card listing available variable keys for easy reference.
  * Button: "Save Template changes".

---

#### Screen 4: Delivery Logs & Queue Tracker (Admin Console)
* **Objective**: Track queue health and retries.
* **Layout**:
  * **Summary KPI widgets**: Active Queue Size, Failed (max retry) count, Average Delivery Latency.
  * **Table Grid**: Displays Recipient name, Gateway Channel, attempt count, last error code (if failed), status, and "Force Retry" click action.

---

## Future Scope

* **Drag-and-Drop HTML email layout builder**: Visual builder (like MJML or Mailchimp editors) to construct responsive email newsletters inside the dashboard (deferred to V2).
