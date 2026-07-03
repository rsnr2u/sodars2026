# Inventory Module: User Interfaces

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail UI/UX designs, screens, panels, and layouts for the Inventory module.

---

## Scope

This document specifies UI screens visible to Advertisers, Providers, and Admins:
* Map-First Discovery view.
* POS-Style Selection components.
* Inventory detail templates.
* Rates manager timeline.
* Branch transfer tool.
* Audit trail charts.

---

## Business Rules

### 1. Google Maps First Navigation
The public marketplace and search portals are fundamentally map-centric:
* **Interface**: The map fills the screen space. A floating search filter overlay is positioned on the left side.
* **Pins**: Active displays are plotted as circular map markers. Hovering over a pin reveals a preview tooltip (e.g. Screen title and baseline Retail Price).
* **Selection Action**: Clicking a pin slides out the screen detail drawer, preserving the current map context behind the drawer.

---

### 2. POS-Style Selection UI
To make slot booking as easy as purchasing items on a Point-of-Sale (POS) terminal, we define the following card pattern:

```text
+-------------------------------------------------+
|               [ Primary Photo ]                 |
|  [Orient: Landscape]             [Type: LED]    |
+-------------------------------------------------+
| Connaught Place LED Wall 1                      |
| Loc: New Delhi | Traffic: 45k/day               |
| Availability: [ Green - Available Today ]       |
+-------------------------------------------------+
| Price/Day: ₹3,000      [x] SELECT / ADD TO CART |
+-------------------------------------------------+
```

* **Visual Assets**: Cards use large, clear cover photographs. Specifications (Resolution, Media Type, Orientation) are represented as icons or color badges.
* **One-Click Select**: A distinct, large "Add to Cart" checkout button is anchored at the bottom of the card block. Clicking the button immediately slides the asset into the floating checkout cart tray without directing the user away from the search page.
* **Multi-Select Modes**: Advertisers can draw a search box area on the map to multi-select and bulk-add all displays inside the boundary box to their cart.

---

### 3. Screen Layout Specifications

#### Screen 1: Inventory List (Provider View)
* **Objective**: Displays listed screens and listing status badges.
* **Layout**:
  * **Header**: Count cards showing `Total Screens`, `Under Review`, and `Live`. Includes "+ List New Screen" button.
  * **Grid**: List cards showing screen thumbnail, name, branch assignment, daily Net Price, and Status Badge (Yellow "Awaiting Approval", Green "Live", Red "Rejected").

---

#### Screen 2: Inventory Details Page (Public/Advertiser View)
* **Objective**: Full product listing overview.
* **Layout**:
  * **Header**: Title, orientation badge, geolocated address, and share links.
  * **Media Carousel**: Swipeable high-definition photo gallery showing the physical display installation.
  * **Sidebar**: Date flight calendar picker, loop frequency selector, and "Complete Booking" action container.
  * **Footer**: Detailed specs grid (Width, Height, aspect ratios, file format rules, active daily runtime schedule).

---

#### Screen 3: Rate Manager (Provider View)
* **Objective**: Configure baseline and seasonal price slots.
* **Layout**:
  * Timeline view displaying custom rates assigned to calendar dates.
  * Form inputs: Start Date, End Date, and Net Price cents/paisa input boxes.

---

## Future Scope

* **Digital Twin Render**: Embedded 3D model renderings of the screen environment (deferred to V2).
