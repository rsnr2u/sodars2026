# Marketplace Module: REST APIs

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the REST API endpoints, request validators, and standard JSON response schemas for the Marketplace module.

---

## Scope

This document specifies API resources used by web and mobile clients to query map pins, filter display inventory, manage favorites, and submit booking requests.

---

## Business Rules

### 1. API Endpoint Specifications

---

#### 1. Get Marketplace Map Pins
* **Method**: `GET`
* **Endpoint**: `/api/v1/marketplace/map`
* **Purpose**: Fetches coordinates of active screens for map plotting.
* **Authentication**: None (Public).
* **Request Query Parameters**:
  * `ne_lat`, `ne_lng`: Northeast map boundary coordinates (required).
  * `sw_lat`, `sw_lng`: Southwest map boundary coordinates (required).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "b32a10bc-d419-482a-a9a3-5c8e23b12345",
        "latitude": 28.6304,
        "longitude": 77.2177,
        "media_type": "led_screen",
        "retail_price_cents": 300000
      }
    ]
  }
  ```

---

#### 2. Get Filtered Inventory List
* **Method**: `GET`
* **Endpoint**: `/api/v1/marketplace/search`
* **Purpose**: Retrieves display details matching filter criteria (paginated).
* **Authentication**: None (Public).
* **Query Parameters**:
  * `city`, `area`, `media_type`, `orientation`, `illuminated`, `min_price_cents`, `max_price_cents`, `start_date`, `end_date`.
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "b32a10bc-d419-482a-a9a3-5c8e23b12345",
        "name": "Connaught Place LED Wall 1",
        "media_type": "led_screen",
        "city": "New Delhi",
        "retail_price_cents": 300000,
        "width_cm": 600,
        "height_cm": 400,
        "primary_photo_url": "https://s3.sodars.com/media/cp_led_1.png"
      }
    ],
    "meta": {
      "current_page": 1,
      "total": 1
    }
  }
  ```

---

#### 3. Toggle Bookmark (Favorite)
* **Method**: `POST`
* **Endpoint**: `/api/v1/marketplace/favorites`
* **Purpose**: Adds or removes an asset from favorites.
* **Authentication**: Bearer Token (Customer).
* **Request Body**:
  ```json
  {
    "inventory_id": "b32a10bc-d419-482a-a9a3-5c8e23b12345"
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Bookmark status toggled successfully.",
    "is_favorite": true
  }
  ```

---

#### 4. Validate Booking Cart Details
* **Method**: `POST`
* **Endpoint**: `/api/v1/marketplace/cart`
* **Purpose**: Validates screen rates, markups, and availability dates before initiating payments.
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
    ]
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "subtotal_cents": 3000000,
      "tax_cents": 540000,
      "total_cents": 3540000,
      "available": true
    }
  }
  ```

---

#### 5. Submit Booking Request
* **Method**: `POST`
* **Endpoint**: `/api/v1/marketplace/booking-request`
* **Purpose**: Submits a purchase request to review.
* **Authentication**: Bearer Token (Customer).
* **Request Body**:
  ```json
  {
    "cart_items": [
      {
        "inventory_id": "b32a10bc-d419-482a-a9a3-5c8e23b12345",
        "start_date": "2026-10-01",
        "end_date": "2026-10-05",
        "daily_frequency": 2
      }
    ],
    "creative_file_path": "customers/c819/creatives/festive_banner.mp4",
    "payment_gateway_ref": "pay_stripe_987654321"
  }
  ```
* **Response (201 Created)**:
  ```json
  {
    "success": true,
    "data": {
      "booking_request_id": "c1d2e3f4-5a6b-7c8d-9e0f-fa26b1234567",
      "status": "pending_audit",
      "estimated_activation": "24 to 48 hours"
    }
  }
  ```

---

## Future Scope

* **Endpoint `POST /api/v1/marketplace/smart-pricing`**: Machine learning endpoint suggesting cost adjustments (deferred to V2).
