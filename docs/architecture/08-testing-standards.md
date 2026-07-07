# Testing Standards

Every bounded context must carry an automated test suite verifying lifecycle, engine, RLS, and platform integrations.

---

## 1. Setup
* Tests must extend `Tests\Core\ApiTestCase` and use `Illuminate\Foundation\Testing\RefreshDatabase`.
* Seed required configuration settings, roles, permissions, and organizations inside `setUp()`.
* If utilizing searchable entities, manually seed `SearchIndex` records inside `setUp()` to prevent refresh database skips.

## 2. Scenarios to Cover
* **RLS isolation**: Querying resources from Organization A must never leak to Organization B.
* **Lifecycle state machine**: Test every transition (Planned -> Validated -> Approved -> Dispatched) and assert corresponding snapshots are created.
* **Projections rebuilding**: Verify event dispatches populate read models correctly.
* **Conflict scanners**: Assert double-bookings and rest period violations raise conflicts.
