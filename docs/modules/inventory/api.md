# Inventory Module: REST APIs

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the REST API contracts for the Inventory module of SODARS, mapping endpoints, search parameters, validation, and JSON schemas.

---

## Scope

This document specifies API resources used by Providers to configure display listings and Customers to query available screens.

---

## Business Rules

### 1. API Endpoint Specifications

---

#### 1. Add Digital Asset (Inventory)
* **Method**: `POST`
* **Endpoint**: `/api/v1/inventories`
* **Purpose**: Register a new display screen in the system.
* **Authentication**: Bearer Token (Provider Admin).
* **Request Body**:
  ```json
  {
    "name": "Connaught Place LED Wall 1",
    "media_type": "led_screen",
    "latitude": 28.6304,
    "longitude": 77.2177,
    "address": "Block A, Connaught Place, New Delhi",
    "city": "New Delhi",
    "state": "Delhi",
    "area": "Connaught Place",
    "width_cm": 600,
    "height_cm": 400,
    "orientation": "landscape",
    "illuminated": 1,
    "net_price_cents": 250000
  }
  ```
* **Validation Rules**:
  * `name`: Required, string, max 150.
  * `latitude`, `longitude`: Required, numeric.
  * `net_price_cents`: Required, integer, minimum 10000.
  * `orientation`: Required, in `portrait`, `landscape`.
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "b32a10bc-d419-482a-a9a3-5c8e23b12345",
      "name": "Connaught Place LED Wall 1",
      "branch_id": "e42e5b7a-89a1-432b-a010-fa26b12a67e2",
      "net_price_cents": 250000,
      "status": "draft"
    }
  }
  ```

---

#### 2. Search & List Inventory (Public)
* **Method**: `GET`
* **Endpoint**: `/api/v1/inventories`
* **Purpose**: Fetches active assets matching filter parameters. Used on the map view.
* **Authentication**: None (Public).
* **Request Parameters**:
  * `city`: string (optional)
  * `media_type`: string (optional)
  * `max_price_cents`: integer (optional)
  * `start_date`, `end_date`: YYYY-MM-DD (optional, availability checks)
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "b32a10bc-d419-482a-a9a3-5c8e23b12345",
        "name": "Connaught Place LED Wall 1",
        "latitude": 28.6304,
        "longitude": 77.2177,
        "media_type": "led_screen",
        "retail_price_cents": 300000,
        "primary_photo_url": "https://s3.sodars.com/media/cp_led_1.png"
      }
    ]
  }
  ```

---

#### 3. Configure Pricing Rate Period
* **Method**: `POST`
* **Endpoint**: `/api/v1/inventories/{id}/rates`
* **Purpose**: Creates custom holiday or seasonal pricing.
* **Authentication**: Bearer Token (Provider Admin).
* **Request Body**:
  ```json
  {
    "rate_cents": 350000,
    "start_date": "2026-10-25",
    "end_date": "2026-11-05"
  }
  ```
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "id": "5f6e7a8b-12ab-4cd3-a90f-fa26b123ab02",
      "inventory_id": "b32a10bc-d419-482a-a9a3-5c8e23b12345",
      "rate_cents": 350000,
      "start_date": "2026-10-25",
      "end_date": "2026-11-05"
    }
  }
  ```

---

#### 4. Audit Screen Listing (Admin Tool)
* **Method**: `PATCH`
* **Endpoint**: `/api/v1/admin/inventories/{id}/status`
* **Purpose**: Approve/reject screen listings.
* **Authentication**: Bearer Token (Super Admin or Branch Manager).
* **Request Body**:
  ```json
  {
    "status": "approved"
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Screen listing approved and published to the marketplace."
  }
  ```

---

## Future Scope

* **Endpoint `POST /api/v1/inventories/bulk-import`**: Direct CSV file parser endpoint to import 100+ screens at once (deferred to V2).
