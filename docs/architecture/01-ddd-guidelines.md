# Domain-Driven Design Guidelines

This document outlines the guidelines for structural modeling in SODARS.

---

## 1. Aggregate Roots
* An Aggregate Root is the sole gatekeeper for its graph of entities.
* Accessing or modifying child entities directly from repositories, controllers, or other contexts is forbidden.
* Aggregate roots must extend `App\Core\Models\BaseBusinessModel`.

## 2. Value Objects
* Value Objects are immutable primitives encapsulating attributes and simple validation rules.
* Properties must be declared as `public readonly` and should not have identity keys.
* Examples: `GeoLocation`, `OptimizationResult`, `RecurrencePattern`.

## 3. Enums
* Use backed enums for states, types, and categories.
* Enums must be cast directly on the Eloquent model casts definition.

## 4. Lifecycle Managers
* Contain state transition machines and invariant validation logic.
* Trigger snapshots, timeline entries, and publish domain events during execution transitions.
