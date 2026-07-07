# Projection Rebuilding Guidelines

Projections are read models updated in response to domain events.

---

## 1. Listeners
* Define a single class (e.g. `ProjectionUpdateListener`) subscribing to all events related to the aggregate context.
* On handle, resolve the aggregate data and recalculate statistics (assigned count, utilization scores, slots availability).
* Save values to projection tables (e.g. `operations_resource_availability_projections`).

## 2. Testing Projections
* Verify that changing state of an aggregate (e.g., assigning a resource) triggers events that rebuild projections correctly.
* Confirm that projections are empty or initialized correctly on database reset.
