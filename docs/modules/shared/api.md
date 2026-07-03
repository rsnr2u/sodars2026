# Shared Module: REST APIs

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the REST API endpoints, request validators, and standard JSON formats for the Shared infrastructure utilities.

---

## Scope

This document specifies API resources used by portal clients to upload assets, query geographics databases, compile print PDFs, and download CSV sheets.

---

## Business Rules

### 1. API Endpoint Specifications

---

#### 1. Central File Upload
* **Method**: `POST`
* **Endpoint**: `/api/v1/shared/upload`
* **Purpose**: Uploads raw media files directly to S3.
* **Authentication**: Bearer Token (Authenticated Users).
* **Request Header**: `Content-Type: multipart/form-data`.
* **Request Payload**:
  * `file`: Binary file upload (Max 5MB for images, 50MB for video).
  * `type`: String (`creative`, `verification_doc`, `payment_slip`).
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "e5b6c7d8-12ab-4cd3-a90f-fa26b12345ab",
      "file_name": "ads_banner_v1.png",
      "file_path": "customers/c1a0/creatives/ads_banner_v1.png",
      "public_url": "https://sodars-assets.s3.amazonaws.com/customers/c1a0/creatives/ads_banner_v1.png",
      "mime_type": "image/png"
    }
  }
  ```

---

#### 2. Get Geographic Cities (Autocomplete/Cascader lookup)
* **Method**: `GET`
* **Endpoint**: `/api/v1/shared/geography/cities`
* **Purpose**: Retrieves list of registered cities.
* **Authentication**: Optional (Public access for marketplace filter menus).
* **Query Parameters**:
  * `district_id`: UUID (optional).
  * `search`: string (optional search string).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "f5e6a7b8-12ab-4cd3-a90f-fa26b12345cd",
        "name": "Noida",
        "district_id": "ea10f2ab-3bc1-49fa-bf2d-fa26b123ab01"
      }
    ]
  }
  ```

---

#### 3. Generate QR Code Stream
* **Method**: `POST`
* **Endpoint**: `/api/v1/shared/utility/qrcode`
* **Purpose**: Renders visual QR codes.
* **Authentication**: Bearer Token (Authenticated Users).
* **Request Body**:
  ```json
  {
    "text": "https://sodars.com/bookings/invoice/c1d2e3f4",
    "size": 250
  }
  ```
* **Response (200 OK)**:
  * Returns direct raw PNG binary image stream.
  * Header: `Content-Type: image/png`.

---

#### 4. Import CSV Records
* **Method**: `POST`
* **Endpoint**: `/api/v1/shared/import`
* **Purpose**: Validates and imports tabular structures.
* **Authentication**: Bearer Token (Admin / Providers only).
* **Request Payload**:
  * `file`: Uploaded CSV/Excel file.
  * `type`: string (`inventory_import`).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Import completed successfully. 45 database records inserted."
  }
  ```

---

## Future Scope

* **Endpoint `POST /api/v1/shared/video/transcode`**: Dispatching S3 video payloads to transcoding pipelines (deferred to V2).
