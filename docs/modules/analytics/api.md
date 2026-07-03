# Analytics Module: REST APIs

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

## Purpose

The purpose of this document is to detail the REST API endpoints, parameters, and standard JSON responses for the Analytics Business Intelligence engine.

---

## Scope

This document specifies API resources used by portal dashboards to fetch KPIs and compile PDF/CSV exports.

---

## Business Rules

### 1. API Endpoint Specifications

---

#### 1. Get Dashboard KPIs
* **Method**: `GET`
* **Endpoint**: `/api/v1/analytics/dashboard`
* **Purpose**: Retrieves overview metrics for dashboard cards.
* **Authentication**: Bearer Token (All authenticated users).
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "occupancy_rate": 68.5,
      "total_screens": 45,
      "total_gross_cents": 125000000,
      "pending_audits_count": 3
    }
  }
  ```

---

#### 2. Get Revenue Timeline Data
* **Method**: `GET`
* **Endpoint**: `/api/v1/analytics/revenue`
* **Purpose**: Retrieves a timeline series of sales revenue for charting.
* **Authentication**: Bearer Token (Super Admin or Branch Manager).
* **Query Parameters**:
  * `start_date`, `end_date`: YYYY-MM-DD.
* **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "labels": ["2026-06-25", "2026-06-26", "2026-06-27"],
      "datasets": [
        {
          "label": "Gross Revenue Cents",
          "data": [3540000, 4800000, 1200000]
        }
      ]
    }
  }
  ```

---

#### 3. Export Analytics Report
* **Method**: `POST`
* **Endpoint**: `/api/v1/analytics/export`
* **Purpose**: Compiles a download report file.
* **Authentication**: Bearer Token (Admin, Provider).
* **Request Body**:
  ```json
  {
    "report_type": "revenue",
    "format": "CSV",
    "start_date": "2026-06-01",
    "end_date": "2026-06-29"
  }
  ```
* **Validation Rules**:
  * `report_type`: Required, in `revenue`, `occupancy`, `inventory`.
  * `format`: Required, in `PDF`, `XLSX`, `CSV`.
* **Response (200 OK)**:
  * Returns binary file stream matching requested type.
  * Header: `Content-Type: text/csv`.

---

## Future Scope

* **Endpoint `GET /api/v1/analytics/predictive`**: AI projections endpoints fetching future quarterly occupancy estimates (deferred to V2).
