# Campaign Module: Workflows

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the operational workflows and logic steps for executing campaigns, reviewing creatives, and uploading proofs of performance.

---

## Scope

This document specifies step-by-step workflows for:
* Auto-creating campaigns upon commercial booking approval.
* Uploading and reviewing creative artwork.
* Map-scheduling active slot indexes.
* Activating, pausing, and resuming campaigns.
* Uploading proof of execution and signing off campaigns as complete.

---

## Business Rules

### 1. Workflow: Create Campaign & Assign Inventory
* **Actor**: System.
* **Steps**:
  1. System listens for a `BookingApproved` event.
  2. System extracts: Customer ID, Branch ID, targeted screens list, dates range, and flight frequencies.
  3. System inserts a record into the `campaigns` table in `draft` status.
  4. System inserts pivot records into `campaign_inventory` for each display screen in the booking.
  5. System triggers notification to Customer to upload creative files. Status shifts to `artwork_pending`.

---

### 2. Workflow: Upload & Review Artwork
* **Actor**: Customer / Branch Manager.
* **Steps**:
  1. Customer uploads creative files.
  2. System checks formats:
     * *Validation*: Formats must match allowed list: `JPG`, `PNG`, `PDF`, `AI`, `PSD`, `CDR`, `ZIP`.
  3. Files are saved to S3. Records are logged in `campaign_creatives` in `pending` status.
  4. Branch Manager reviews assets for legal compliance.
  5. Actions:
     * **Approve Creative**: Status shifts to `approved`. Campaign status shifts to `scheduled`. Date slots indexes are booked in the `campaign_schedule` table.
     * **Reject Creative**: Creative status shifts to `rejected`. Manager enters rejection reason. Campaign status shifts back to `artwork_pending`. Email alerts are sent to the customer to re-upload.

---

### 3. Workflow: Campaign Execution (Start / Pause / Resume)
* **Actor**: System (Cron Job) / Branch Manager.
* **Steps**:
  1. **Start**: A daily server cron checker runs. If current date equals campaign `start_date` and status is `scheduled`:
     * Status transitions to `running`.
  2. **Pause**: Branch Manager can pause campaigns (e.g. for regional emergency alerts or disputes):
     * Status shifts to `paused`.
  3. **Resume**: Manager toggles resume. Status reverts back to `running`.

---

### 4. Workflow: Upload Proof of Execution & Completion
* **Actor**: Provider Admin / Branch Manager.
* **Steps**:
  1. Provider displays the ads on physical screens.
  2. Provider takes site photos/videos showing display active status.
  3. Provider logs in, navigates to Campaign -> **Upload Proof**.
  4. Inputs: Uploads photo/video files directly to S3. Enters verification notes.
  5. System creates record in `campaign_proofs` in `pending` status.
  6. Branch Manager verifies proof files. If correct:
     * Status shifts to `verified`.
  7. When campaign flight `end_date` is reached:
     * Status transitions to `completed`.
     * System compiles proof logs and downloads, and builds the final completion report.

---

## Future Scope

* **Auto AI Proof verification**: Verifying photos using AI image models to confirm correct ad copy was played (deferred to V2).
