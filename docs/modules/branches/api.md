# Branch Module: REST APIs

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the REST API contracts for the Branch module, defining endpoints, parameters, validation requirements, and standard JSON formats.

---

## Scope

This document specifies the endpoints used by the Admin and Branch portals to interface with the Laravel backend. All endpoints require token-based bearer authentication.

---

## Business Rules

### 1. API Endpoint Specifications

---

#### 1. Create Branch
* **Method**: `POST`
* **Endpoint**: `/api/v1/admin/branches`
* **Purpose**: Registers a new regional business branch.
* **Authentication**: Bearer Token (Super Admin only).
* **Request Headers**:
  ```http
  Authorization: Bearer <sanctum_token>
  Accept: application/json
  Content-Type: application/json
  ```
* **Request Body**:
  ```json
  {
    "name": "Branch India North",
    "code": "IN-NORTH",
    "timezone": "Asia/Kolkata",
    "currency_code": "INR",
    "markup_percentage": 15,
    "support_email": "north.support@sodars.com",
    "support_phone": "+911145678901"
  }
  ```
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "e42e5b7a-89a1-432b-a010-fa26b12a67e2",
      "name": "Branch India North",
      "code": "IN-NORTH",
      "timezone": "Asia/Kolkata",
      "currency_code": "INR",
      "markup_percentage": 15,
      "support_email": "north.support@sodars.com",
      "support_phone": "+911145678901",
      "is_active": 1,
      "created_at": "2026-06-29T15:10:00Z"
    }
  }
  ```

---

#### 2. Update Branch Settings
* **Method**: `PUT`
* **Endpoint**: `/api/v1/admin/branches/{id}`
* **Purpose**: Modifies branch profile fields.
* **Authentication**: Bearer Token (Super Admin or assigned Branch Manager).
* **Request Body**:
  ```json
  {
    "support_email": "north.support-v2@sodars.com",
    "support_phone": "+911145678902",
    "markup_percentage": 18
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "e42e5b7a-89a1-432b-a010-fa26b12a67e2",
      "name": "Branch India North",
      "code": "IN-NORTH",
      "timezone": "Asia/Kolkata",
      "currency_code": "INR",
      "markup_percentage": 18,
      "support_email": "north.support-v2@sodars.com",
      "support_phone": "+911145678902",
      "is_active": 1,
      "updated_at": "2026-06-29T15:12:00Z"
    }
  }
  ```

---

#### 3. Toggle Branch Activation Status
* **Method**: `PATCH`
* **Endpoint**: `/api/v1/admin/branches/{id}/status`
* **Purpose**: Deactivates or reactivates a branch.
* **Authentication**: Bearer Token (Super Admin only).
* **Request Body**:
  ```json
  {
    "is_active": 0
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Branch deactivated successfully. All linked screen listings are hidden."
  }
  ```

---

#### 4. Add Coverage City
* **Method**: `POST`
* **Endpoint**: `/api/v1/admin/branches/{id}/coverage`
* **Purpose**: Registers a city inside the branch's service range.
* **Authentication**: Bearer Token (Super Admin or assigned Branch Manager).
* **Request Body**:
  ```json
  {
    "city_name": "Noida",
    "state_name": "Uttar Pradesh"
  }
  ```
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "cb104e76-d189-4fa2-bf4f-6d2c4b787123",
      "branch_id": "e42e5b7a-89a1-432b-a010-fa26b12a67e2",
      "city_name": "Noida",
      "state_name": "Uttar Pradesh"
    }
  }
  ```

---

#### 5. List Branches
* **Method**: `GET`
* **Endpoint**: `/api/v1/admin/branches`
* **Purpose**: Retrieves a list of all branches (paginated).
* **Authentication**: Bearer Token (Super Admin only).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "e42e5b7a-89a1-432b-a010-fa26b12a67e2",
        "name": "Branch India North",
        "code": "IN-NORTH",
        "is_active": 1
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "total": 1
    }
  }
  ```

---

## Future Scope

* **Endpoint `GET /api/v1/admin/branches/{id}/performance`**: Deferring detailed hourly metric queries to V2.
