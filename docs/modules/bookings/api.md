# Booking Module: REST APIs

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the REST API endpoints, request validators, and standard JSON response schemas for the Booking module.

---

## Scope

This document specifies API resources used by customers to submit checkouts and branch managers/providers to execute workflow transitions and record offline payments.

---

## Business Rules

### 1. API Endpoint Specifications

---

#### 1. Create Booking Request
* **Method**: `POST`
* **Endpoint**: `/api/v1/bookings`
* **Purpose**: Submits a new booking request.
* **Authentication**: Bearer Token (Customer).
* **Request Body**:
  ```json
  {
    "items": [
      {
        "inventory_id": "b32a10bc-d419-482a-a9a3-5c8e23b12345",
        "start_date": "2026-10-01",
        "end_date": "2026-10-05",
        "daily_frequency": 2
      }
    ],
    "payment_method_intended": "bank_transfer",
    "payment_reference_intended": "TXN_987654321_OFFLINE",
    "creative_file_path": "customers/c1a0/creatives/ads_banner.mp4"
  }
  ```
* **Validation Rules**:
  * `items`: Required, array, minimum 1.
  * `payment_method_intended`: Required, in `cash`, `bank_transfer`, `cheque`, `upi`, `neft`, `rtgs`.
  * `creative_file_path`: Required, S3 file path matching allowed extensions.
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "c1d2e3f4-5a6b-7c8d-9e0f-fa26b1234567",
      "customer_id": "c1a0c7e2-89a1-432b-a010-fa26b12a67a9",
      "branch_id": "e42e5b7a-89a1-432b-a010-fa26b12a67e2",
      "total_price_cents": 3000000,
      "status": "pending",
      "created_at": "2026-06-29T15:23:00Z"
    }
  }
  ```

---

#### 2. Record Offline Payment (Admin Tool)
* **Method**: `POST`
* **Endpoint**: `/api/v1/bookings/{id}/payments`
* **Purpose**: Records offline transaction validation.
* **Authentication**: Bearer Token (Super Admin or Branch Manager).
* **Request Body**:
  ```json
  {
    "payment_method": "bank_transfer",
    "amount_cents": 3540000,
    "reference_number": "TXN_987654321_OFFLINE"
  }
  ```
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "message": "Offline payment successfully recorded. Booking status shifted to branch_review."
  }
  ```

---

#### 3. Transition Booking Status (Approve / Reject)
* **Method**: `POST`
* **Endpoint**: `/api/v1/bookings/{id}/status`
* **Purpose**: Approves or rejects booking request.
* **Authentication**: Bearer Token (Super Admin, Branch Manager, or Provider).
* **Request Body**:
  ```json
  {
    "action": "approve",
    "comment": "Screens confirmed and available for deployment."
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "c1d2e3f4-5a6b-7c8d-9e0f-fa26b1234567",
      "status": "approved",
      "updated_at": "2026-06-29T15:25:00Z"
    }
  }
  ```

---

#### 4. Cancel Booking
* **Method**: `POST`
* **Endpoint**: `/api/v1/bookings/{id}/cancel`
* **Purpose**: Cancels booking and releases calendar blocks.
* **Authentication**: Bearer Token (Customer, Branch Manager, or Super Admin).
* **Request Body**:
  ```json
  {
    "comment": "Customer requested budget cancellation."
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Booking request successfully cancelled. Inventory dates capacity has been released."
  }
  ```

---

## Future Scope

* **Endpoint `POST /api/v1/bookings/{id}/refund`**: Triggering payment gateway automated credit card voids (deferred to V2).
