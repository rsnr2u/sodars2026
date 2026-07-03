# Shared Module: Workflows

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail operational workflows, calculations pipelines, and common utility steps for Shared infrastructure.

---

## Scope

This document specifies step-by-step workflows for:
* Centralized file uploads and image thumbnail processing.
* Barcode, QR, and PDF file generations.
* Bulk CSV imports validations.
* Google Geocoding coordinate lookups.
* Audit and Activity logging execution.
* Daily storage cleanup.

---

## Business Rules

### 1. Workflow: Centralized File Upload & Image Processing
* **Actor**: User / Application.
* **Steps**:
  1. Frontend sends file data to `/api/v1/shared/upload`.
  2. System runs **Validation Helper**:
     * Verifies file size matches max limits (e.g. video files max 50MB, images max 5MB).
     * Validates file extensions (allow list checks).
  3. System streams file payload directly to secure AWS S3 bucket directory.
  4. If file is an image (`JPG`, `PNG`):
     * System invokes **Image Manager**:
       * Generates a compressed thumbnail version (width 150px, maintaining ratio).
       * Saves thumbnail file to S3.
  5. System inserts metadata record in `media_library`.
  6. Returns S3 public URLs and database UUID key to frontend.

---

### 2. Workflow: Google Geocoding & GPS Resolution
* **Actor**: System (Inventory listing creation / Customer signup).
* **Steps**:
  1. Caller module calls **Geocoding API Wrapper**, providing address parameters.
  2. System issues request to Google Places / Google Geocoding endpoint.
  3. Google API returns JSON coordinates (Latitude, Longitude).
  4. System extracts coordinates.
  5. System validates coordinates:
     * Checks if coordinates fall within the managing branch geographic boundary.
  6. Returns verified GPS coordinates back to caller module.

---

### 3. Workflow: Bulk CSV Import
* **Actor**: Admin / Provider (e.g. Import Inventory list).
* **Steps**:
  1. User uploads a CSV file.
  2. System saves file to S3, registers entry in `temporary_files` with 24-hour expiration tag.
  3. System parses CSV header schema:
     * *Validation*: Column keys must match target schema layout exactly.
  4. System initiates database transaction:
     * Loops through CSV lines.
     * Runs cell validation helpers.
     * If errors are found on *any* line:
       * Transaction rolls back immediately.
       * System compiles list of errors (with row numbers) and returns JSON feedback.
     * If all lines pass:
       * Commits database changes.
       * Logs import action in `imports`.

---

### 4. Workflow: Audit & Activity Logging
* **Actor**: System Event Dispatcher.
* **Steps**:
  1. Triggered automatically by database model events (creates, updates, deletes).
  2. Log worker extracts: User ID, IP Address, Model Class, Record ID, and array of altered columns (old vs new states).
  3. Log worker inserts record to `audit_logs` or `activity_logs`.
  4. *Security Check*: Event action is append-only. Update/Delete operations on logs are blocked at the database driver configuration level.

---

## Future Scope

* **Video Transcoding queue**: Hooking S3 uploads directly to AWS Elemental MediaConvert (deferred to V2).
