# Provider Module: REST APIs

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the REST API endpoints, validation requirements, and standard JSON formats for the Provider module of SODARS.

---

## Scope

This document specifies the endpoints used by display owners to register accounts, upload documents, edit banking details, invite staff, and update settings.

---

## Business Rules

### 1. API Endpoint Specifications

---

#### 1. Provider Registration
* **Method**: `POST`
* **Endpoint**: `/api/v1/providers/register`
* **Purpose**: Self-registers a new provider business and admin user.
* **Authentication**: None (Public).
* **Request Body**:
  ```json
  {
    "company_name": "Metro LED Displays Ltd",
    "registration_number": "GSTIN987654321A",
    "city": "Noida",
    "state": "Uttar Pradesh",
    "contact_name": "Rajesh Kumar",
    "email": "rajesh@metroled.com",
    "phone": "+919876543210",
    "password": "SecurePassword123",
    "password_confirmation": "SecurePassword123"
  }
  ```
* **Validation Rules**:
  * `company_name`: Required, max 150.
  * `registration_number`: Required, unique in `providers` table.
  * `email`: Required, unique in `users` table, valid email format.
  * `phone`: Required, minimum 10 digits.
  * `password`: Required, minimum 8 characters, confirmed.
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "provider_id": "a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d",
      "company_name": "Metro LED Displays Ltd",
      "default_branch_id": "e42e5b7a-89a1-432b-a010-fa26b12a67e2",
      "status": "draft",
      "user": {
        "id": "f89e12c4-8a10-43bc-9d0a-fa26b12c87ab",
        "email": "rajesh@metroled.com",
        "role": "provider_admin"
      }
    }
  }
  ```

---

#### 2. Upload Compliance Document Details
* **Method**: `POST`
* **Endpoint**: `/api/v1/providers/documents`
* **Purpose**: Saves file information for uploaded credentials.
* **Authentication**: Bearer Token (Provider Admin).
* **Request Body**:
  ```json
  {
    "document_type": "tax_certificate",
    "file_path": "providers/a1b2c3d4/documents/gst_cert.pdf"
  }
  ```
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "c78d9e10-12ab-4bc3-9d0f-fa26b12a8901",
      "provider_id": "a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d",
      "document_type": "tax_certificate",
      "file_path": "providers/a1b2c3d4/documents/gst_cert.pdf",
      "status": "pending"
    }
  }
  ```

---

#### 3. Update Payout Bank Details
* **Method**: `POST`
* **Endpoint**: `/api/v1/providers/bank-account`
* **Purpose**: Configures the bank account details for payouts.
* **Authentication**: Bearer Token (Provider Admin).
* **Request Body**:
  ```json
  {
    "bank_name": "State Bank of India",
    "account_holder": "Metro LED Displays Ltd",
    "account_number": "123456789012",
    "routing_code": "SBIN0001234"
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Payout bank details updated successfully."
  }
  ```

---

#### 4. Add Staff Member
* **Method**: `POST`
* **Endpoint**: `/api/v1/providers/staff`
* **Purpose**: Registers a staff user to access the provider portal.
* **Authentication**: Bearer Token (Provider Admin).
* **Request Body**:
  ```json
  {
    "name": "Amit Sharma",
    "email": "amit@metroled.com",
    "password": "TempPassword123",
    "role": "provider_staff"
  }
  ```
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "d01e12fa-3bc1-49fa-bf2d-fa26b12345ab",
      "name": "Amit Sharma",
      "email": "amit@metroled.com",
      "role": "provider_staff"
    }
  }
  ```

---

#### 5. Verify / Suspend Provider Account (Admin Tool)
* **Method**: `PATCH`
* **Endpoint**: `/api/v1/admin/providers/{id}/status`
* **Purpose**: Update a provider's status badge.
* **Authentication**: Bearer Token (Super Admin or Branch Manager).
* **Request Body**:
  ```json
  {
    "status": "verified"
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Provider account status updated to verified."
  }
  ```

---

## Future Scope

* **Endpoint `POST /api/v1/providers/billing/upgrade`**: Automated subscription tier scaling via direct Stripe interface integrations (deferred to V2).
