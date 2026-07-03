# Settings Module: User Interfaces

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the visual layouts, navigation structures, and input settings sheets for the Settings module in the Admin Portal.

---

## Scope

This document specifies UI screens visible exclusively to Super Admins (Head Office):
* Settings Dashboard.
* Category Left Navigation.
* Custom Configuration Sub-panels (Branding, Pricing, Security, API Gateways).
* Backup Manager panel.
* Cache & Version Manager controls.

---

## Business Rules

### 1. Screen Layout Specifications

#### Screen 1: Settings Dashboard
* **Objective**: Central workspace config tree.
* **Layout**:
  * **Unified Layout Split**:
    * **Left Navigation Sidebar**: Sticky vertical sidebar containing category links:
      * `Company Profile`, `Branding & Theme`, `Pricing & Markups`, `Taxes & Invoices`, `Email SMTP`, `SMS / WhatsApp Providers`, `Google Maps API`, `AWS S3 Storage`, `Security & Auth`, `Feature Flags`, `Backup Manager`, `Cache Controller`.
    * **Right Detail Panel**: Renders the active settings panel selected in the left navigation.

---

#### Screen 2: Detail Settings Panel Templates

* **Company Profile Tab**:
  * Form inputs for Legal Name, Tax Registry (GSTIN), and physical address coordinates.
* **Branding & Theme Tab**:
  * Vector SVG/PNG uploader widget for the system logo.
  * Left/Right primary and secondary color palette picker widgets.
* **Pricing & Markups Tab**:
  * Input numeric slider for Default Maximum Markup (restricted to range 0% - 20%).
* **Email SMTP / SMS / WhatsApp Tabs**:
  * Text fields for Host, Port, User, and Passwords.
  * *UI Masking Rule*: API keys, passwords, and tokens must render as masked points (`••••••••`) after saving. Clicking a padlock icon prompts confirmation before exposing plaintext.
* **Storage (S3) / Maps Tabs**:
  * Config fields for S3 bucket names, credentials, regions, and Google Maps API keys.

---

#### Screen 3: Backup Manager
* **Objective**: Manage database snapshots.
* **Layout**:
  * **Header**: Large action button: **Run Database Backup**.
  * **Table Grid**: Displays list of files, sizes (MB), timestamps, and a download S3 backup link.

---

#### Screen 4: Cache & Version Manager
* **Objective**: System diagnostics.
* **Layout**:
  * **Cache Actions Card**: Large red buttons labeled **Flush Settings Cache (Redis)** and **Rebuild Config Logs**.
  * **Version Board**: Displays active git version tags, database driver specifications, server uptime details, and system feature flags lists.

---

## Future Scope

* **Multilingual Translation Grid**: Form sheet displaying side-by-side localization keys edits for translations (deferred to V2).
