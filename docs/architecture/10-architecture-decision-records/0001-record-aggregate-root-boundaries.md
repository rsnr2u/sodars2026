# ADR 0001: Separation of Planning and Execution boundaries

## Context
In planning platforms, planners configure schedules (what should happen) while telemetry feeds and workers log execution details (what is actually happening). Mixing these concerns in a single database record causes data overrides, concurrency lockouts, and poor audit histories.

## Decision
We decided to split these concerns into two separate Aggregate Roots:
1. `Schedule`: Stores planned parameters (times, assignments, templates, recurrence rules). Captured as an immutable snapshot upon approval or dispatch.
2. `ScheduleExecution`: Stores live tracking data (actual durations, active ETAs, distance traveled, timeline event logs).

## Consequences
* Planners can compare the planned schedule versus actual executions for BI analytics.
* Concurrency overrides are prevented since telemetry feeds update execution records without writing to the planning aggregate.
