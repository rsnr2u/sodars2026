# ADR 0002: Event Versioning Strategy

## Context
As SODARS grows, domain event payloads evolve. Listener systems must remain backward compatible to support old event records during replay actions and prevent runtime decoder crashes when upgrading platforms.

## Decision
We enforce the following versioning rules:
* Every business event class must define a protected integer `$eventVersion = 1` field.
* Changing, deleting, or re-typing existing fields inside the event `$data` payload is prohibited.
* New fields can be appended as nullable variables.
* If a breaking payload schema change is mandatory, increment the `$eventVersion` variable and implement specific listener logic branches to handle old versus new version versions.

## Consequences
* Event streams are resilient and fully replayable.
* Upgrading event payloads does not break downstream listeners.
