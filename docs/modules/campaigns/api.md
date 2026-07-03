# Campaign Module: REST APIs

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the REST API endpoints, request validators, and standard JSON formats for the Campaign module.

---

## Scope

This document specifies API resources used by Customers to submit creative banners and Providers/Admins to verify schedules and log execution proofs.

---

## Business Rules

### 1. API Endpoint Specifications

---

#### 1. Upload Campaign Creative (Artwork)
* **Method**: `POST`
* **Endpoint**: `/api/v1/campaigns/{id}/artwork`
* **Purpose**: Submits ad creative files details.
* **Authentication**: Bearer Token (Customer).
* **Request Body**:
  ```json
  {
    "file_name": "summer_sale_10s.mp4",
    "file_path": "customers/c1a0/creatives/summer_sale_10s.mp4",
    "file_type": "MP4"
  }
  ```
* **Validation Rules**:
  * `file_name`: Required, string.
  * `file_path`: Required, S3 file reference key.
  * `file_type`: Required, in `JPG`, `PNG`, `PDF`, `AI`, `PSD`, `CDR`, `ZIP`, `MP4`.
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "f5e6a7b8-12ab-4cd3-a90f-fa26b12345ef",
      "campaign_id": "c1d2e3f4-5a6b-7c8d-9e0f-fa26b1234567",
      "file_path": "customers/c1a0/creatives/summer_sale_10s.mp4",
      "status": "pending_review"
    }
  }
  ```

---

#### 2. Upload Proof of Execution
* **Method**: `POST`
* **Endpoint**: `/api/v1/campaigns/{id}/proof`
* **Purpose**: Uploads photo/video evidence of ad execution.
* **Authentication**: Bearer Token (Provider Admin).
* **Request Body**:
  ```json
  {
    "inventory_id": "b32a10bc-d419-482a-a9a3-5c8e23b12345",
    "file_path": "providers/p102/proofs/cp_wall_run_shot.jpg",
    "notes": "Ad is live on display loop slot 3."
  }
  ```
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "ea10f2ab-3bc1-49fa-bf2d-fa26b123ab99",
      "campaign_id": "c1d2e3f4-5a6b-7c8d-9e0f-fa26b1234567",
      "status": "pending_verification"
    }
  }
  ```

---

#### 3. Verify Creative / Proof (Admin Tool)
* **Method**: `POST`
* **Endpoint**: `/api/v1/admin/campaigns/{id}/verify`
* **Purpose**: Auditor updates creative or proof compliance state.
* **Authentication**: Bearer Token (Super Admin or Branch Manager).
* **Request Body**:
  ```json
  {
    "target": "creative",
    "target_id": "f5e6a7b8-12ab-4cd3-a90f-fa26b12345ef",
    "action": "approve",
    "rejection_reason": null
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Creative approved successfully. Campaign status updated to scheduled."
  }
  ```

---

#### 4. Get Campaigns List
* **Method**: `GET`
* **Endpoint**: `/api/v1/campaigns`
* **Purpose**: Retrieves campaigns linked to the user's account.
* **Authentication**: Bearer Token (Customer, Provider, or Admin).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "c1d2e3f4-5a6b-7c8d-9e0f-fa26b1234567",
        "name": "Summer Promotion Campaign",
        "status": "running",
        "start_date": "2026-10-01",
        "end_date": "2026-10-05"
      }
    ]
  }
  ```

---

## Future Scope

* **Endpoint `GET /api/v1/campaigns/{id}/telemetry`**: Telemetry tracking loop plays counts (deferred to V2).
