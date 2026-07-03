# Campaign Module: Business Rules

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the business logic rules, constraints, validations, and verification requirements applied to Campaigns.

---

## Scope

This document specifies the rules governing creative formats, scheduling validations, and proof-of-execution uploads.

---

## Business Rules

### 1. Verification Dependencies & Inputs
* **Approved Booking Mandate**: A Campaign cannot be created or configured without a pre-existing commercial `booking_id` in `Approved` status.
* **Creative Artwork Mandate**: A Campaign cannot transition from `artwork_pending` to `scheduled` until the customer uploads ad creatives and the files are marked `approved` by the governing Branch Manager.
* **Supported Creative Formats**:
  * *Image/Document formats*: `JPG`, `PNG`, `PDF`, `AI`, `PSD`, `CDR`.
  * *Compressed formats*: `ZIP` (used for sending massive canvas designs).
  * *Video formats*: `MP4` (restricted to max 50MB file size).
* **Scheduling Bounds**: The campaign play calendar dates must align precisely with the commercial flight start and end dates defined in `booking_items`. A campaign schedule cannot extend beyond the booked dates window.

---

### 2. Operational Control & Permissions
* **Branch Override**: Only authorized Branch Managers can trigger campaigns state toggles (e.g. pausing, resuming, or verifying completion reports).
* **Provider Proof Upload**:
  * Providers must upload visual evidence verifying ad playback (Proof of Execution).
  * Proof items must contain:
    * At least one photograph or video file path (S3 stored).
    * Execution logs and notes.
    * Upload timestamp.
    * Mapped user ID of the provider staff who uploaded the file.
* **Customer Read-Only Access**: Customers can view uploaded proofs and download final completion reports, but cannot delete or edit proof files once saved in the database.

---

### 3. Immutable Records
* **Reconciliation Auditing**: Completed and archived campaign logs are immutable. Any adjustments to completed campaigns require manual database admin intervention and are logged in system security trails.

---

## Future Scope

* **Automated AI Content Compliance Check**: Image scanning models validating uploaded banners for local compliance policy terms automatically (deferred to V2).
