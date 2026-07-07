# SODARS Frontend Architecture Principles

This document defines the core guidelines governing the front-end architecture.

---

## 1. Modules Own Themselves
No feature module may import components, hooks, or assets directly from another module. Inter-module communication must happen exclusively via shared libraries (`@sodars/contracts`, `@sodars/sdk`) or platform adapters.

## 2. Pages Never Call APIs
Components and page views must not run raw HTTP calls or directly call Axios/Fetch. They must consume custom TanStack Query hooks, which delegate to standard repository interfaces in `@sodars/api`.

## 3. Separation of Concerns
Components are strictly visual render engines. business calculations, validation schemas, and application routing state are managed by custom hooks, Zustand stores, and modules, keeping views declarative and easily testable.

## 4. Everything Registers Itself
Portals (like `apps/admin`) are layout hosts. Specific features (e.g., campaigns, CRM) register their own routes, widgets, and sidebar menu items dynamically using the `ModuleSDK`.

## 5. Design System is the Single Source of Truth
All user interface elements (Buttons, Cards, Dialogs, Virtual Tables, Forms) must be consumed from the `@sodars/design-system` package. Direct usage of external styling libraries inside portals is prohibited.

## 6. OpenAPI-Driven Contracts
All API request payloads and query responses (DTOs) are compiled directly from the Laravel OpenAPI spec inside `@sodars/contracts` to prevent manual interface duplication.

## 7. Declarative Permissions
Enforce user authorization parameters declaratively using the `PermissionGate` component (e.g. `<PermissionGate can="campaigns.create">`) rather than raw conditional checks.

## 8. Lazy Loading
Every portal module must be dynamic-imported and lazy-loaded to ensure rapid initial page load speeds and tiny assets chunk sizes.
