# Mobile Module: Business Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the business logic rules, data synchronization constraints, offline limits, and security protocols for Mobile apps.

---

## Scope

This document specifies the guidelines that all smartphone installations, local storage, API sync tasks, and device permissions checks must enforce.

---

## Business Rules

### 1. Offline Operations & Sync Boundaries
* **Maximum Offline Duration**:
  * Users can perform offline actions (e.g. queueing proofs) for a maximum of **7 days** without internet connection.
  * If a device stays offline longer than 7 days, local SQLite database entries expire, tokens are invalidated, and the user is forced to log in online again.
* **Conflict Resolution**:
  * In case of update conflicts (local sync logs vs. backend database modifications), the server state wins. Mobile client must drop its local queue changes and sync fresh database parameters.
* **Offline Media uploads queue**:
  * Media files captured while offline are stored in compressed formats in the local sandbox container directory. Files are uploaded sequentially to S3 before executing JSON endpoints calls.

---

### 2. Device Session Controls & Rules
* **Device Registration**:
  * Every smartphone device running the app must be registered in the backend database.
* **One Active Session per Device**:
  * A user ID can have multiple registered devices, but is restricted to a **single active login session** on each physical device UUID. Logging in on a second device does not invalidate sessions on the first, but double logs on the *same device UUID* overrides active sessions instantly.
* **Mandatory Updates Enforcement**:
  * If the API check response (`/api/v1/mobile/version`) returns `is_update_mandatory = true`, the mobile app must display an overlay blocking user interactions, redirecting the user to the App Store or Google Play Store to execute updates.

---

### 3. Permissions & Security Mandates
* **Hardware Permissions Check**:
  * The app must prompt for Camera and GPS Location permissions.
  * If GPS or Camera access is denied by the user, the app must block listing edits and installation proofs uploads, rendering a descriptive warning notice.
* **Storage Encryption**:
  * Local SQLite cache databases must utilize SQLCipher wrapper encryption using keys stored in the Android Keystore / iOS Keychain.
* **Sensitive Page screenshot blocks**:
  * The React Native client must implement secure flags (`FLAG_SECURE` on Android, screen recording monitors on iOS) to block users from capturing screenshots of screens displaying financial analytics dashboards or secure bank credentials pages.

---

## Future Scope

* **Geofencing tasks automatic dispatch**: Pushing task lists notifications when a worker steps within a display coordinates boundary (deferred to V2).
