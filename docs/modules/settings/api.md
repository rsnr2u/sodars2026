# Settings Module: REST APIs

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the REST API endpoints, request validators, authorization restrictions, and standard JSON formats for the Settings module.

---

## Scope

This document specifies API resources used exclusively by the Admin Portal (Super Admin users) to manage global system configurations and clear cache systems.

---

## Business Rules

### 1. API Endpoint Specifications

---

#### 1. Retrieve Global Settings
* **Method**: `GET`
* **Endpoint**: `/api/v1/admin/settings`
* **Purpose**: Fetches system settings sorted by categories.
* **Authentication**: Bearer Token (Super Admin only).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "company_name": "SODARS Ltd",
      "default_markup_percentage": 20,
      "maintenance_mode_active": 0,
      "active_features": {
        "whatsapp_alerts": true,
        "push_notifications": false
      }
    }
  }
  ```

---

#### 2. Update Pricing Settings
* **Method**: `POST`
* **Endpoint**: `/api/v1/admin/settings/pricing`
* **Purpose**: Configures baseline pricing markup ceilings.
* **Authentication**: Bearer Token (Super Admin only).
* **Request Body**:
  ```json
  {
    "default_markup_percentage": 18
  }
  ```
* **Validation Rules**:
  * `default_markup_percentage`: Required, integer, range 0 to 20.
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Pricing settings updated. Cache cleared automatically."
  }
  ```

---

#### 3. Update Email/SMTP Settings (Encrypted value check)
* **Method**: `POST`
* **Endpoint**: `/api/v1/admin/settings/email`
* **Purpose**: Sets mailer integration variables. Sensitive values are encrypted on write.
* **Authentication**: Bearer Token (Super Admin only).
* **Request Body**:
  ```json
  {
    "smtp_host": "smtp.sendgrid.net",
    "smtp_port": 587,
    "smtp_username": "apikey",
    "smtp_password": "SG.SensitiveSendGridPasswordHere"
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Mailer SMTP settings updated successfully. Credentials encrypted."
  }
  ```

---

#### 4. Clear & Rebuild Settings Cache
* **Method**: `POST`
* **Endpoint**: `/api/v1/admin/settings/cache/rebuild`
* **Purpose**: Forces system settings cache rebuild from database values.
* **Authentication**: Bearer Token (Super Admin only).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Redis configuration cache rebuilt successfully."
  }
  ```

---

#### 5. Update Feature Flags
* **Method**: `POST`
* **Endpoint**: `/api/v1/admin/settings/feature-flags`
* **Purpose**: Toggles active feature states.
* **Authentication**: Bearer Token (Super Admin only).
* **Request Body**:
  ```json
  {
    "flag_key": "enable_whatsapp_alerts",
    "is_enabled": 1
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Feature flag state updated."
  }
  ```

---

## Future Scope

* **Endpoint `POST /api/v1/admin/settings/white-label`**: Submitting custom domain maps and assets links for white-label branches (deferred to V2).
