# ADR-005: Foundation Freeze

**Status**: Accepted  
**Date**: 2026-06-29  
**Decision Makers**: SODARS Architecture Team  
**Supersedes**: None  

---

## Context

The SODARS backend has been developed as a Modular Monolith using Laravel 12. Over the course of Phase 1, the following enterprise-grade architectural patterns and infrastructure components were introduced:

### Foundation Layer

| Component | Description |
|---|---|
| Four-Layer Modular Monolith | Core / Modules / Platform / Infrastructure separation |
| Module Architecture | Presentation → Application → Domain → Infrastructure per module |
| Repository + Service + Action | Thin controllers, business logic in services and actions |
| CQRS Readiness | Separated Command and Query buses |
| Domain Events | Event-driven module communication |
| Value Objects | Money, Currency, Coordinates, DateRange, etc. |
| Specification Pattern | Composable query criteria |
| Pipelines | Chainable processing stages |
| State Management | Centralized StateService |
| Module Registry | Explicit module registration and caching |
| UUID-First Design | All primary keys use UUIDs |
| Soft Deletes | All domain models support soft deletion |
| Audit Columns | created_by, updated_by, deleted_by on all tables |
| DTO Layer | Data Transfer Objects for all cross-layer communication |

### Reliability Layer

| Component | Description |
|---|---|
| Transactional Outbox | CloudEvents-inspired payload with SELECT FOR UPDATE SKIP LOCKED |
| Inbox Deduplication | Prevents duplicate event processing |
| Idempotency Keys | Database-backed request deduplication |
| Distributed Locking | LockService with execute/acquire/release APIs |
| Dead Letter Queue | Automatic promotion after configurable retry limit |
| Exponential Backoff | Automatic retry scheduling with increasing delays |

### Observability Layer

| Component | Description |
|---|---|
| Correlation ID | Request-scoped X-Correlation-ID propagation |
| Trace ID | End-to-end tracing through X-Trace-ID |
| Causation ID | Event chain tracking through causation_id |
| TraceContext | Request-scoped container for all trace identifiers |
| Health Endpoints | Three-tier health checks (live / ready / details) |

### Operations Layer

| Component | Description |
|---|---|
| Module Commands | module:cache, module:clear, module:list, module:status, module:discover |
| Cleanup Commands | outbox:cleanup, inbox:cleanup, idempotency:cleanup |
| Dead Letter Retry | outbox:retry with --id, --all, --dry-run support |
| Scheduled Cleanup | Automated daily/hourly cleanup via Laravel Scheduler |

---

## Decision

**The SODARS foundation architecture is now frozen at Version 1.**

### Policy

1. **No new architectural patterns** may be introduced into Version 1 unless they solve a **demonstrated production issue** that cannot be addressed with the existing patterns.

2. **New business modules** must conform to the frozen architecture:
   - Follow the four-layer module structure
   - Use the established Repository + Service + Action pattern
   - Register through the ModuleRegistry
   - Use existing Value Objects and DTOs
   - Emit events through the OutboxService
   - Consume events through the Inbox pattern

3. **Any architectural change** must be documented in a new ADR before implementation.

4. **Exceptions** require explicit justification documenting:
   - The production issue being solved
   - Why existing patterns are insufficient
   - Impact assessment on existing modules
   - Migration plan for affected modules

---

## Business Module Development Sequence

The following modules will be developed using the frozen foundation:

| Order | Module | Domain |
|---|---|---|
| 1 | Branches | Branch management, locations, operating hours |
| 2 | Providers | Service provider registration and management |
| 3 | Inventory | Equipment, categories, availability |
| 4 | Marketplace | Listings, pricing, search |
| 5 | Customers | Customer profiles, preferences, history |
| 6 | Bookings | Reservation lifecycle, payments, calendar |
| 7 | Campaigns | Promotions, discounts, marketing |
| 8 | Notifications | Multi-channel notification delivery |
| 9 | Analytics | Reporting, dashboards, KPIs |
| 10 | Mobile APIs | Mobile-optimized API endpoints |

---

## Module Compliance Checklist

Every new business module MUST include:

- [ ] `module.json` manifest with name, version, enabled, providers, permissions
- [ ] Four-layer directory structure (Presentation/Application/Domain/Infrastructure)
- [ ] Module service provider registered in ModuleRegistry
- [ ] Versioned API routes under `Presentation/Routes/v1/`
- [ ] Repository interfaces in Domain layer with implementations in Infrastructure
- [ ] Request validation via Form Requests
- [ ] DTOs for cross-layer data transfer
- [ ] Domain events emitted through OutboxService
- [ ] Feature tests with minimum coverage
- [ ] Database migrations with UUIDs, soft deletes, and audit columns

---

## Consequences

### Positive

- **Consistency**: All modules share the same patterns, reducing cognitive load
- **Velocity**: Developers can focus on business logic instead of architectural decisions
- **Testability**: Established patterns have known testing strategies
- **Onboarding**: New team members learn one architecture, applicable everywhere
- **Upgrade Safety**: Frozen architecture reduces risk when upgrading Laravel or PHP versions

### Negative

- **Rigidity**: Novel requirements may need to work within existing patterns
- **Tech Debt**: If patterns prove suboptimal, migration across all modules is required
- **Overhead**: The four-layer structure adds initial boilerplate per module

### Mitigations

- Architecture review meetings are scheduled before each major module begins
- ADR process ensures changes are deliberate and documented
- Module boundaries allow isolated refactoring without cross-module impact

---

## Configuration Reference

All foundation settings are centralized in `config/foundation.php`:

```php
return [
    'trace' => ['enabled' => true],
    'outbox' => [
        'retry_limit' => 5,
        'batch_size' => 100,
        'cleanup_days' => 30,
    ],
    'idempotency' => ['ttl_hours' => 24],
    'locks' => ['default_ttl' => 60],
    'health' => ['details_requires_auth' => true],
];
```

---

## Event Versioning Policy

Domain events follow a versioned naming convention:

```
{domain}.{action}.v{version}
```

Examples:
- `booking.created.v1`
- `booking.approved.v1`
- `branch.updated.v1`

Rules:
- Payloads are **never modified in place** after deployment
- Breaking changes require a **new event version** (e.g., `booking.created.v2`)
- Old versions remain supported for at least one release cycle
- Schema version is tracked in the outbox `schema_version` column

---

## Developer Governance Note

> **Infrastructure changes are allowed only for bug fixes, security fixes, performance improvements, or ADR-approved architectural changes. New business modules must conform to the frozen foundation.**

Any developer proposing a change to the `Core/`, `Platform/`, or `Infrastructure/` layers must:

1. Open a discussion with the architecture team
2. Document the rationale in a new ADR
3. Demonstrate that existing patterns cannot solve the problem
4. Obtain explicit approval before implementation

Business module development (`Modules/`) does **not** require a new ADR as long as it follows the Module Compliance Checklist above.

---

## References

- ADR-001: Branch Business Model
- ADR-002: Marketplace Pricing
- ADR-003: Provider Ownership
- ADR-004: Version 1 Scope
