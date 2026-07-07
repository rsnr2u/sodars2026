# Multi-Tenancy & RLS Guidelines

Every tenant interaction inside SODARS is isolated via `organization_id` scopes.

---

## 1. Schema
* Every aggregate, child entity, timeline, and projection table must have a non-nullable `organization_id` column.
* Create indexes on `organization_id` to speed up tenant-level lookups.

## 2. Query Scopes
* Enforce tenant checks dynamically.
* Leverage Laravel global scopes or automated filters to ensure users never access data belonging to other organizations.
* RLS tests must assert that a tenant query returns only records belonging to that specific tenant.
