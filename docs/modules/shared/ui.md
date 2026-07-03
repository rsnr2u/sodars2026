# Shared Module: User Interfaces

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the specifications and designs for reusable UI widgets and common layout templates across portals.

---

## Scope

This document specifies the standard component models:
* Dropzone Upload components.
* Media Picker and file browsers modals.
* Google Map Pickers.
* Geographic cascading selectors.
* Standard system feedbacks overlays (Dialogs, Loaders, Empty boards).

---

## Business Rules

### 1. Reusable Component Specifications

#### Component 1: File Upload Dropzone
* **Objective**: Drag-and-drop file receiver.
* **Layout**:
  * Rectangular dotted boundary area.
  * Vector upload cloud icon with text instructions ("Click to upload or drag files here").
  * File format support indicator chip tags.
  * Real-time file processing upload loading bar.
  * Thumbnail pre-viewer box showing active crops hooks.

---

#### Component 2: Google Map Picker Modal
* **Objective**: Interface to capture GPS pins coordinates.
* **Layout**:
  * **Header**: Google Places autocomplete search bar.
  * **Center**: Full-width interactive Map interface rendering standard marker pins.
  * **Footer**: Text boxes displaying extracted latitude, longitude, and formatted street address attributes.
  * Action: **Confirm Coordinates Pin**.

---

#### Component 3: Geographic Cascading Selector Group
* **Objective**: Normalized geographic selector menus.
* **Layout**:
  * Horizontal stack of 5 selector boxes:
    * `Country` -> `State` -> `District` -> `City` -> `Pincode`.
  * *UI Binding rule*: Selecting a state dynamically queries the database API and unlocks the District drop-down list. Sub-selectors remain locked/disabled until parent categories are selected.

---

#### Component 4: Document & Video Player Modal
* **Objective**: Standard preview panel overlay.
* **Layout**:
  * Modal drawer overlaying dashboards screens.
  * Dynamically matches mime-types:
    * *Images*: Full-screen zoom carousel.
    * *PDFs*: Embedded PDF viewer panel with download toggle.
    * *Videos (MP4)*: Vanilla HTML5 video container.

---

#### Component 5: Standard Feedback Dialog Modals
All dashboards must consume these standardized layouts for notifications:
* **Confirmation Dialog**: Action warning modal displaying text descriptions and buttons ("Cancel", "Confirm").
* **Delete Dialog**: Modal indicating high risk. Prompts user validation before execution.
* **Loader Indicator**: Centered SVG spinning wheel overlay blocking page interactions during API delays.
* **Empty State Widget**: Placeholder card showing illustration graphic, warning title, and description (e.g. "No Bookings Found").

---

## Future Scope

* **Drag-and-Drop Image Cropper Component**: Drag interface to crop artwork templates inside the browser (deferred to V2).
