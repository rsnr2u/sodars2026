# SODARS Platform Execution Roadmap

This document serves as the single source of truth for the implementation progress of the SODARS Modular Monolith Platform.

---

## 1. Core Architecture Freezes
* [x] **Backend Architecture v1.0** (DDD, Events, CQRS, RLS, Search, Webhooks, Reports)
* [x] **Frontend Platform Foundation v1.1** (Monorepo Workspace, Turborepo, SDK Registry)

---

## 2. Phase F2 — Platform Shell Execution Sprints
* [ ] **Sprint 1: Platform Bootstrap**
  * Initialize Turborepo workspaces task caching.
  * Implement `@sodars/providers` (AppProviders, ThemeProvider, QueryProvider).
  * Build `@sodars/layout` layout shells.
  * Standardize `@sodars/design-system` base primitives.
* [ ] **Sprint 2: Authentication & Multi-Tenancy**
  * Integrate `@sodars/auth` (Zustand state authStore, tenantStore).
  * Build React Hook Form + Zod Login layout.
  * Support active organization tenant switching.
* [ ] **Sprint 3: Platform Shell & Navigation**
  * Collapsible Sidebar displaying menus dynamically.
  * Top navigation panel trace breadcrumbs.
  * Light, Dark, and System theme mode integrations.
* [ ] **Sprint 4: Pluggable Registries**
  * Dynamic module loading in `@sodars/app-registry`.
  * Pluggable dashboard widget mounting via `WidgetSDK`.
  * Pluggable shortcut, notification, and Command Palette inputs (`Ctrl + K`).
* [ ] **Sprint 5: Infrastructure & Telemetry**
  * Generate OpenAPI client definition.
  * Integrate TanStack Query queryClient hooks.
  * Integrate feature flags gates and client exception logs telemetry.
* [ ] **Sprint 6: Reference Module — IAM**
  * User roles, permissions, active tenant management screens.
  * Verify full portal routing guards validation.

---

## 3. Long-Term Modules Roadmap
* [ ] **IAM** (Identity)
* [ ] **CRM** (Leads, Quotations)
* [ ] **Campaigns** (Ads, Placements)
* [ ] **Providers** (Partner Portal)
* [ ] **Inventory** (Availability spaces)
* [ ] **Bookings** (Client contracts)
* [ ] **Wallet** (Settlements ledger)
* [ ] **Finance** (Invoices logs)
* [ ] **Transport** (Fleet, Telemetry heartbeats)
* [ ] **Operations** (Enterprise Scheduling, Executions dispatcher)
* [ ] **Analytics** (Reports dashboard)
