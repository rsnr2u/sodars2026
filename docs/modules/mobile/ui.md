# Mobile Module: User Interfaces

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the visual layouts, panels, screen flow designs, and responsive requirements for the SODARS Mobile applications.

---

## Scope

This document specifies mobile UI components:
* Login, OTP, and Dashboard views.
* Interactive map list split screens.
* Hardware scanner interfaces (Camera, QR codes).
* Offline synchronization progress queues.
* Light / Dark mode guidelines and tablet grids rules.

---

## Business Rules

### 1. Navigation Flow & Screens

#### Screen 1: Login & OTP Onboarding
* **Visual Elements**:
  * Clean system logo header, email/password inputs, and "Enable FaceID" toggle.
  * OTP screen features a 6-digit numeric input layout block that auto-focuses on load, with a resend timer countdown.

---

#### Screen 2: Interactive Discovery Map (Marketplace Tab)
* **Visual Elements**:
  * Google Map layer spanning full viewport.
  * Floating category marker tags (Billboard, LED Screen, Bus Shelter).
  * Pin markers colored by availability state (Green = Available, Grey = Fully booked).
  * Sliding bottom sheet showing brief asset details card (cover image, pricing, and "Add to Cart" checkout button).

---

#### Screen 3: Camera & QR/Barcode Scanner Overlays
* **Visual Elements**:
  * Camera frame overlay drawing alignment boundaries (target guidelines framing).
  * Flash toggle icon.
  * QR Code scanner renders a green laser line animation scanning over barcode frames. If scan registers:
    * Automatically triggers redirection routing.

---

#### Screen 4: Local Offline Sync Queue
* **Visual Elements**:
  * Visual banner showing "Offline Mode - Connection Lost".
  * Queue page showing pending actions list (e.g. "Upload installation proof for Delhi Metro").
  * Renders status chips indicators:
    * `Failed (Retry Scheduled)`, `Queued`, `Syncing`.
  * Red trigger: **Clear Offline Cache**.

---

### 2. Responsive Rules & Styling Guidelines
* **Dark Mode**: All screens must support system-wide dark mode configurations. High contrast ratios must meet standard guidelines (WCAG AA compliance).
* **Tablet Layouts**: If viewport width exceeds 600px (e.g. iPads/Android Tablets), app dashboard switches to a two-column detail layout (Left: Data tables list, Right: Detail card panel).
* **Touch Targets**: Buttons, input boxes, and list items must have touch targets of at least **$48 \times 48\text{ px}$** to ensure clean interaction.
