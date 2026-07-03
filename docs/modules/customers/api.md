# Customer Module: REST APIs

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the REST API contracts, input validators, and response formats for the Customer module.

---

## Scope

This document specifies API resources used by advertisers to register, view orders, manage addresses, and fetch PDF billing files.

---

## Business Rules

### 1. API Endpoint Specifications

---

#### 1. Customer Self-Registration
* **Method**: `POST`
* **Endpoint**: `/api/v1/customers/register`
* **Purpose**: Registers a new customer user and profile.
* **Authentication**: None (Public).
* **Request Body**:
  ```json
  {
    "name": "Global Brands Ltd",
    "email": "advertising@globalbrands.com",
    "password": "SecretPassword123",
    "password_confirmation": "SecretPassword123",
    "category": "corporate",
    "city": "Noida",
    "state": "Uttar Pradesh",
    "tax_id": "GSTIN123456789B"
  }
  ```
* **Validation Rules**:
  * `name`: Required, max 150.
  * `email`: Required, unique in `users` table.
  * `category`: Required, in `individual`, `corporate`, `government`, `political_party`.
  * `password`: Required, minimum 8 characters, confirmed.
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "customer_id": "c1a0c7e2-89a1-432b-a010-fa26b12a67a9",
      "name": "Global Brands Ltd",
      "category": "corporate",
      "status": "active",
      "default_branch_id": "e42e5b7a-89a1-432b-a010-fa26b12a67e2"
    }
  }
  ```

---

#### 2. Get Customer Profile
* **Method**: `GET`
* **Endpoint**: `/api/v1/customers/profile`
* **Purpose**: Retrieves current customer info.
* **Authentication**: Bearer Token (Customer).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "c1a0c7e2-89a1-432b-a010-fa26b12a67a9",
      "name": "Global Brands Ltd",
      "category": "corporate",
      "status": "active",
      "tax_id": "GSTIN123456789B",
      "billing_address": {
        "street_address": "101 Commercial Zone",
        "city": "Noida",
        "state": "Uttar Pradesh",
        "zip_code": "201301"
      }
    }
  }
  ```

---

#### 3. Upload Verification Document
* **Method**: `POST`
* **Endpoint**: `/api/v1/customers/documents`
* **Purpose**: Submit authorization details for government/political profiles.
* **Authentication**: Bearer Token (Customer).
* **Request Body**:
  ```json
  {
    "doc_type": "party_affiliation",
    "file_path": "customers/c1a0c7e2/documents/cert.pdf"
  }
  ```
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "cb102e86-12ab-4fa2-bf4f-6d2c4b787901",
      "customer_id": "c1a0c7e2-89a1-432b-a010-fa26b12a67a9",
      "doc_type": "party_affiliation",
      "file_path": "customers/c1a0c7e2/documents/cert.pdf",
      "status": "pending"
    }
  }
  ```

---

#### 4. Get Customer Bookings List
* **Method**: `GET`
* **Endpoint**: `/api/v1/customers/bookings`
* **Purpose**: Returns the list of all campaign orders placed by this customer.
* **Authentication**: Bearer Token (Customer).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "d5e6f7a8-12ab-4cd3-a90f-fa26b1234567",
        "total_price_cents": 3540000,
        "status": "pending_audit",
        "created_at": "2026-06-29T15:15:00Z"
      }
    ]
  }
  ```

---

## Future Scope

* **Endpoint `POST /api/v1/customers/balance`**: Credit checks for postpaid client balance querying (deferred to V2).
