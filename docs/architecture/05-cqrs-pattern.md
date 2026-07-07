# CQRS Pattern Guidelines

SODARS decouples operational mutations (writes) from listing data displays (reads) to ensure horizontal scalability.

---

## 1. Write Aggregates
* Handles transaction writes, locks sequences, and coordinates validation checks.
* Focuses on data consistency and enforcement of business invariants.

## 2. Read Projections
* Read-only tables optimized for display queries (no joins).
* Rebuilt exclusively asynchronously or on-commit via event listeners.
* Controllers and lifecycle managers must never write to projection models directly.
