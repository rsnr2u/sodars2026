# SODARS Enterprise Architecture Principles

This document defines the core principles governing the architecture of the SODARS Modular Monolith.

---

## 1. Domain First
Every business capability belongs to exactly one bounded context. Cross-domain communication must occur through versioned domain events—never direct repository mutations or direct cross-module service dependencies.

## 2. Aggregate Root Rule
Each bounded context has one authoritative Aggregate Root. No child entity may publish events, enforce cross-aggregate invariants, or coordinate workflows independently. All writes must flow through the Lifecycle Manager of the Aggregate Root.

## 3. Lifecycle Rule
Every state transition must flow through a Lifecycle Manager. Mutating business state directly (bypassing the lifecycle managers and state machines) is strictly prohibited.

## 4. Events are Contracts
Every published domain event is an immutable contract. Events must include metadata (event name, aggregate ID, organization ID, event version, timestamp, correlation/causation ID) and maintain backward compatibility.

## 5. CQRS Read Model Rule
Operational screens, UI endpoints, and dashboards must query read-only projection tables. Transaction models should not be queried for real-time list views.

## 6. Multi-Tenancy Rule
Every business aggregate must belong to a tenant and contain `organization_id`. Tenant boundaries are enforced automatically via database query scopes and row-level security.

## 7. Audit Rule
Every meaningful business transition must independently trigger:
1. An audit event record.
2. A transactional outbox event.
3. A local event bus dispatch.

## 8. Search Rule
Search indexes are projections. The search engine is never the system of record. Rebuild indexes dynamically from the event streams.

## 9. Reporting Rule
Report classes aggregate already-valid operational data. Business validation and rules belong inside the aggregates and lifecycle managers, never in the reports.

## 10. Platform Independence Rule
Infrastructure modules (Search, Audit, Webhooks, Notifications, DAM) must remain adapters. Business domains must interact with abstractions, allowing clean replacement of underlying infrastructure.
