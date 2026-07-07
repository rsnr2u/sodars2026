# ADR 0001: Monorepo and Turborepo Workspace Architecture

## Context
Deploying four distinct portal web applications (Admin, Provider, Customer, Operations) as completely isolated repositories leads to copy-pasting UI elements, duplicates API clients, breaks authentication consistency, and complicates branding updates.

## Decision
We decided to adopt a Monorepo workspace governed by Turborepo:
* Shared elements reside under `packages/` (`design-system`, `auth`, `sdk`, `contracts`, `api`, `core`, `testing`).
* Portals reside under `apps/` and import packages locally.
* Turborepo manages parallel build caching and task pipelines.

## Consequences
* 100% component and API model sharing.
* Shared build and testing workflows.
* Extremely rapid CI pipelines via caching.
