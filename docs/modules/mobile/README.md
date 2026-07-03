# Module: Mobile App

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to introduce the Mobile module, which details the architectural design and operations of the SODARS cross-platform smartphone application (React Native for Android and iOS).

---

## Scope

This document specifies:
* React Native client wrapper architecture.
* Supported platform targets and device capabilities (GPS, Camera, Push notifications, offline caching).
* Offline synchronization queues.
* Deep linking structures and version update checks.

---

## Business Rules

### 1. Unified Mobile Apps Suite
Rather than compilation into six isolated apps, the SODARS mobile codebase targets a **single react native app build** containing conditional role-based dashboard routers:

* **Provider Dashboard**: Lets display owners list screens, upload verification details, and inspect booking payments metrics on-site.
* **Branch Manager Dashboard**: Lets regional managers review active campaigns list, verify uploads compliance, and sign off execution proofs.
* **Field Staff Dashboard**: Simple workflow interface to check active display tasks, capture physical verification photos/videos, and perform geocoding audits.
* **Customer Dashboard**: Lets advertisers discover screens on the map, review invoices, check live flight progress, and view proof of execution galleries.
* **Super Admin Dashboard**: Consolidated dashboard for Head Office monitoring.
* **Guest Marketplace**: Public browse map page showing screens search criteria.

---

### 2. Device Integration Standards
* **Secure Storage**: JWT tokens and session data must be saved to secure locations only (iOS Keychain and Android Keystore/EncryptedSharedPreferences).
* **Location Tracking (GPS)**: GPS coordinate tracking must only execute when actively listing screens or uploading site proofs. Background location tracking is blocked in Version 1.
* **Deep Linking**: Standard deep link routes schema: `sodars://bookings/{id}` or `sodars://campaigns/{id}`.

---

## Future Scope

* **Background Geofencing**: Automatically pinging field workers when they cross regional branch geographic coordinates boundaries (deferred to V2).
