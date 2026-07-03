# Mobile Module: Workflows

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the user workflows, sync protocols, and offline operations for the SODARS Mobile applications.

---

## Scope

This document specifies step-by-step workflows for:
* Authentication (OTP validation, biometrics).
* Offline data capture and queue syncing.
* Conflict resolution logic.
* Hardware controls (GPS, Camera, Barcode/QR scanning).
* Triggering operational updates (booking reviews, campaign proof uploads).

---

## Business Rules

### 1. Workflow: App Login & Device Registration
* **Actor**: All Users.
* **Steps**:
  1. User enters Email and Password in app inputs.
  2. System verifies inputs and calls Auth API.
  3. System triggers **OTP Verification**:
     * System SMS gateway sends a 6-digit numeric OTP code.
     * User enters code. If verified, access token is generated.
  4. System registers device:
     * App requests hardware specifications (Platform, OS, Device UUID).
     * Mapped record is written in `mobile_devices`.
  5. User can enable **Biometric Login**:
     * Device prompts TouchID / FaceID check.
     * App saves authorization key in Keychain / Keystore.

---

### 2. Workflow: Offline Capture & Synchronization
* **Actor**: Field Worker / Provider.
* **Steps**:
  1. App loses network connection (App registers offline status).
  2. Field Worker captures a verification proof photo and coordinates.
  3. System bypasses API calls, saves image binary to local app sandbox storage, and writes JSON action detail to local SQLite queue database (`offline_sync_queue`).
  4. When network connection is recovered:
     * App executes background synchronization check.
     * Loops through SQLite queue, reads tasks chronologically.
     * Streams S3 files first, then hits target endpoints (e.g. `/api/v1/campaigns/{id}/proof`).
     * On API 200 OK: Removes sync job from local database.

---

### 3. Workflow: Sync Conflict Resolution
* **Actor**: System (Mobile Queue sync loop).
* **Steps**:
  1. Offline queue executes sync request (e.g. updating display status coordinates).
  2. If backend API returns `409 Conflict` (meaning another administrator updated the display while the app was offline):
     * **Resolution Logic**:
       * *Server Wins Rule*: Backend database state takes priority. Mobile app drops local offline changes.
       * *Alert User*: App displays alert card: "Local changes discarded. Screen coordinates were updated by Admin Delhi."

---

### 4. Workflow: Campaign Proof Upload
* **Actor**: Provider Admin / Field Staff.
* **Steps**:
  1. Worker opens Campaign screen, clicks **Upload Proof**.
  2. App requests GPS location permissions (must check accuracy within 50 meters).
  3. App calls local **Camera Manager**:
     * Launches physical camera lens view.
     * Worker captures screen installation photo.
  4. App compresses image to optimize file size.
  5. App saves geocode coordinates in EXIF photo metadata, uploads photo to S3 via `/api/v1/shared/upload`, and dispatches payload to `/api/v1/campaigns/{id}/proof`.

---

## Future Scope

* **Background Location tracking**: Polling worker location intervals to verify transit routes mapping (deferred to V2).
