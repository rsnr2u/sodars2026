# Lifecycle Pattern Guidelines

All aggregate transitions must execute under state machines governed by specialized managers.

---

## 1. Mutations flow
1. **Controller** validates HTTP payload and forwards payload data to an **Action/Service**.
2. **Action/Service** invokes `OperationsLifecycleService` (or similar Context Façade).
3. **Lifecycle Service** delegates to specific **Lifecycle Managers** (e.g. `ScheduleLifecycleManager`).
4. **Lifecycle Manager** runs state transitions, updates database models, commits transaction outbox logs, writes timelines, and dispatches versioned events.

## 2. Snapping & Audit
* Historical planning parameters must be locked as immutable snapshots (`ScheduleSnapshot`) upon state milestones (Approved, Optimized, Dispatched).
* Detailed transitions must write timeline event traces.
