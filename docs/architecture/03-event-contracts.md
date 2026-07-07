# Event Contract Guidelines

Domain events act as public contracts connecting bounded contexts.

---

## 1. Structure
All domain events must extend `App\Core\Events\BusinessEvent` and carry:
* `$aggregateId`: UUID of the primary aggregate.
* `$eventVersion`: Integer representing event version (starts at 1).
* `$data`: Array of event payload details.
* `$timestamp`: ISO 8601 creation date string.

## 2. Backward Compatibility
* Fields must not be deleted or modified in type inside `$data`.
* New fields can be appended as nullable variables.
* If breaking changes are required, increment the `$eventVersion` property and maintain multiple listeners to support old payloads during transitions.
