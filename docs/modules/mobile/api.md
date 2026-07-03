# Mobile Module: REST APIs

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the mobile-specific REST API endpoints, parameter requirements, and JSON responses.

---

## Scope

This document specifies API resources used by mobile apps to register device profiles, upload push tokens, and verify client build version limits.

---

## Business Rules

### 1. API Endpoint Specifications

---

#### 1. Register Mobile Device
* **Method**: `POST`
* **Endpoint**: `/api/v1/mobile/devices`
* **Purpose**: Registers client hardware on platform database.
* **Authentication**: Bearer Token (All users).
* **Request Body**:
  ```json
  {
    "device_uuid": "F88D02B6-0A00-4740-9E11-23BFA1C1AB22",
    "os_platform": "ios",
    "os_version": "16.1.0",
    "app_version": "1.0.0"
  }
  ```
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "c1f2e3f4-5a6b-7c8d-9e0f-fa26b1234567",
      "device_uuid": "F88D02B6-0A00-4740-9E11-23BFA1C1AB22"
    }
  }
  ```

---

#### 2. Sync FCM Push Token
* **Method**: `POST`
* **Endpoint**: `/api/v1/mobile/tokens`
* **Purpose**: Saves Firebase Cloud Messaging token.
* **Authentication**: Bearer Token (All users).
* **Request Body**:
  ```json
  {
    "device_uuid": "F88D02B6-0A00-4740-9E11-23BFA1C1AB22",
    "push_token": "fcm_token_string_here_1234567890"
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "FCM device push token successfully synchronized."
  }
  ```

---

#### 3. Client Build Version Check
* **Method**: `GET`
* **Endpoint**: `/api/v1/mobile/version`
* **Purpose**: Compares client app build version against global settings requirements to trigger mandatory updates.
* **Authentication**: Optional.
* **Query Parameters**:
  * `platform`: `ios` or `android`.
  * `current_version`: string (e.g. `1.0.0`).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "is_update_mandatory": true,
      "latest_supported_version": "1.1.0",
      "app_store_url": "https://apps.apple.com/app/sodars"
    }
  }
  ```

---

## Future Scope

* **Endpoint `POST /api/v1/mobile/location/stream`**: Streaming location logs arrays for GPS trace diagnostics (deferred to V2).
