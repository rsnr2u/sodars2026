# Notifications Module: REST APIs

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the REST API endpoints, request validators, and standard JSON formats for the Notifications module.

---

## Scope

This document specifies API resources used by client dashboards to fetch notifications, mark items as read, modify preferences, and compose administrative broadcasts.

---

## Business Rules

### 1. API Endpoint Specifications

---

#### 1. Get In-App Notifications
* **Method**: `GET`
* **Endpoint**: `/api/v1/notifications`
* **Purpose**: Retrieves a paginated list of in-app alerts.
* **Authentication**: Bearer Token (All authenticated users).
* **Query Parameters**:
  * `unread_only`: boolean (optional).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "a9e12f4b-12ab-4cd3-a90f-fa26b12345ab",
        "title": "Booking Request Submitted",
        "body": "Your booking request for CP LED Screen has been submitted.",
        "status": "delivered",
        "is_read": false,
        "created_at": "2026-06-29T15:28:00Z"
      }
    ]
  }
  ```

---

#### 2. Mark Notification as Read
* **Method**: `POST`
* **Endpoint**: `/api/v1/notifications/{id}/read`
* **Purpose**: Updates notification read status.
* **Authentication**: Bearer Token (All authenticated users).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Notification marked as read."
  }
  ```

---

#### 3. Update User Preferences
* **Method**: `POST`
* **Endpoint**: `/api/v1/notifications/preferences`
* **Purpose**: Saves user channel subscription limits.
* **Authentication**: Bearer Token (All users).
* **Request Body**:
  ```json
  {
    "preferences": [
      {
        "event_key": "booking_cancelled",
        "channel": "sms",
        "is_enabled": 0
      }
    ]
  }
  ```
* **Validation Rules**:
  * `preferences`: Required, array.
  * `preferences.*.event_key`: Required, string.
  * `preferences.*.channel`: Required, in `email`, `sms`, `whatsapp`, `push`, `in_app`.
  * `preferences.*.is_enabled`: Required, boolean (0 or 1).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Notification preferences updated successfully."
  }
  ```

---

#### 4. Update Email/SMS Templates (Admin Tool)
* **Method**: `PUT`
* **Endpoint**: `/api/v1/admin/notification-templates/{id}`
* **Purpose**: Modifies template body texts.
* **Authentication**: Bearer Token (Super Admin only).
* **Request Body**:
  ```json
  {
    "subject_template": "Order Verified: {{booking_number}}",
    "body_template": "Dear {{customer_name}}, payment of {{amount}} is verified for booking {{booking_number}}."
  }
  ```
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Notification template updated successfully."
  }
  ```

---

## Future Scope

* **Endpoint `POST /api/v1/admin/notifications/broadcast`**: Scheduling targeted marketing email broadcasts to customer segment lists (deferred to V2).
