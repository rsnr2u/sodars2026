# ADR 010: Scheduling Platform Component

> **This document represents the finalized Version 1 architecture. Any new feature outside Version 1 must be documented under `12-future-roadmap.md` before implementation.**

* **Title**: ADR 010: Scheduling Platform Component
* **Status**: Approved
* **Date**: 2026-07-01

---

## Context

Different modules in SODARS require calendar mathematical operations, date range logic, and conflict checks:
* **Campaigns** require date segment checks.
* **Bookings** require validation of requested dates.
* **Inventory Availability** requires block/maintenance window overlap tests.

Embedding identical date-overlap logic across multiple modules violates the DRY (Don't Repeat Yourself) principle and risks validation divergence.

---

## Decision

We introduce a centralized, reusable **Scheduling** domain capability under `Platform/Scheduling`.

### 1. Value Objects

* **DateRange**: Immutable date range value object, providing containment checks (`contains`), overlap tests (`overlaps`), range intersection (`intersect`), enclosure verification (`encloses`), and daily division (`toDailySegments`).
* **TimeSlot**: Daily operational window definition (e.g. 06:00 to 22:00) with duration math.
* **WorkingHours**: Operational time limits indexed by day-of-week.
* **HolidayCalendar**: Standard registry for holiday lookup checks.

### 2. Services

* **ConflictDetector**: Service class identifying intersections between candidate schedules and existing occupied slots.
* **CalendarService**: Slices date ranges into monthly, weekly, or daily grids, and counts effective working days.

### 3. Namespace

All scheduling features reside under the namespace `App\Platform\Scheduling` and are completely decoupled from database models.

---

## Consequences

* **Advantages**:
  * Centralizes date math logic into a highly tested, reusable location.
  * Standardizes date overlap validations across Campaigns, Bookings, and Availability.
  * Completely database-agnostic domain value objects.
* **Disadvantages**:
  * Slightly higher architecture complexity, but pays off immediately by simplifying downstream Booking and Pricing modules.
