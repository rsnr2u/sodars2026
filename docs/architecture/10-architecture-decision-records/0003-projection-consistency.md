# ADR 0003: Projection Consistency Model

## Context
High-scale listing queries and dashboard screens suffer from slow response times when joining complex normalized transactional tables. Querying the write models directly during heavy dispatches leads to lock contentions and performance bottlenecks.

## Decision
We decouple write models from read queries using eventually consistent projections:
* Write models focus solely on transaction consistency and trigger domain events during status changes.
* Independent event listeners subscribe to these events and rebuild projection tables asynchronously or on-commit.
* Projection tables are entirely read-only. No controller or manager is permitted to modify them.
* In the case of projection failures, they are completely rebuildable by replaying past events.

## Consequences
* High-speed reading and listing operations.
* Zero transaction locks on the write models from search queries.
